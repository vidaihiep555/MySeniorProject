using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Navigation;
using Microsoft.Phone.Controls;
using Microsoft.Phone.Shell;
using Microsoft.Phone.Maps.Controls;
using UberRiding.Global;
using Windows.Devices.Geolocation;
using System.Device.Location;
using Newtonsoft.Json.Linq;
using Newtonsoft.Json;
using System.Threading.Tasks;
using Microsoft.AspNet.SignalR.Client;
using System.Net.Http;
using Windows.Web.Http;
using UberRiding.Request;
using Microsoft.Phone.Maps.Services;

namespace UberRiding.Customer
{
    public partial class CustomerMainMap : PhoneApplicationPage
    {
        MapLayer mainMapLayer = new MapLayer();
        string start_point = "none";
        //List<MapOverlay> listMainMapOvelay = new List<MapOverlay>();
        DriverRootObject root = null;
        List<Itinerary> itineraries = new List<Itinerary>();

        Geocoordinate myGeocoordinate = null;
        GeoCoordinate myGeoCoordinate = null;

        ReverseGeocodeQuery geoQ = null;

        List<Driver2> list = new List<Driver2>();

        private IHubProxy HubProxy { get; set; }
        const string ServerURI = "http://52.11.206.209:8080/signalr";
        //const string ServerURI = "http://localhost:8080/signalr";
        private HubConnection con { get; set; }

        public CustomerMainMap()
        {
            InitializeComponent();
        }

        protected override void OnNavigatedTo(NavigationEventArgs e)
        {
            base.OnNavigatedTo(e);
            //InitCurrentLocationInfo();
            geoQ = new ReverseGeocodeQuery();
            geoQ.QueryCompleted += geoQ_QueryCompleted;
            if (geoQ.IsBusy == true)
            {
                geoQ.CancelAsync();
            }
            ConnectAsync();
            getDrivers();
        }

        void geoQ_QueryCompleted(object sender, QueryCompletedEventArgs<IList<MapLocation>> e)
        {
            throw new NotImplementedException();
        }

        /*void geoQ_QueryCompleted(object sender, QueryCompletedEventArgs<IList<MapLocation>> e)
        {
            if (e.Result.Count() > 0)
            {
                string showString = e.Result[0].Information.Name;
                showString = showString + "";
                showString = showString + "" + e.Result[0].Information.Address.HouseNumber + " " + e.Result[0].Information.Address.Street;
                showString = showString + "" + e.Result[0].Information.Address.PostalCode + " " + e.Result[0].Information.Address.City;
                showString = showString + "" + e.Result[0].Information.Address.Country + " " + e.Result[0].Information.Address.CountryCode;
                //showString = showString + "\nDescription: ";
                //showString = showString + "\n" + e.Result[0].Information.Description.ToString();

                start_point = showString;

            }
        }*/

        private async void ConnectAsync()
        {
            con = new HubConnection(ServerURI);
            con.Closed += Connection_Closed;
            con.Error += Connection_Error;
            HubProxy = con.CreateHubProxy("MyHub");
            //Handle incoming event from server: use Invoke to write to console from SignalR's thread
            HubProxy.On<string, string>("getPos", (driver_id, message) =>
                Dispatcher.BeginInvoke(() => test(message))
            );
            try
            {
                await con.Start();
            }
            catch (HttpRequestException)
            {
                //No connection: Don't enable Send button or show chat UI
                //btntrack.Content = "eror";
            }
            catch (HttpClientException)
            {
                //btntrack.Content = "eror";
            }

            Dispatcher.BeginInvoke(() =>
            {
                string id = "C" + Global.GlobalData.user_id;
                HubProxy.Invoke("Connect", id);
            });
        }


        private void test(string message)
        {
            string[] latlng = message.Split(",".ToCharArray());
            //double lat = Double.Parse(latlng[0]);
            //double lng = Double.Parse(latlng[1]);
            //addMarkertoMap(new GeoCoordinate(lat, lng));
            //txtFireBase.Text = message;
        }

        private void Connection_Error(Exception obj)
        {
            //txtFireBase.Text = "error";
        }

        /// <summary>
        /// If the server is stopped, the connection will time out after 30 seconds (default), and the 
        /// Closed event will fire.
        /// </summary>
        private void Connection_Closed()
        {
            //Deactivate chat UI; show login UI. 
        }

        public async void InitCurrentLocationInfo()
        {
            Task<GeoCoordinate> x = ShowMyCurrentLocationOnTheMap();


            myGeoCoordinate = await ShowMyCurrentLocationOnTheMap();
        }

        #region Map
        private async Task<GeoCoordinate> ShowMyCurrentLocationOnTheMap()
        {
            // Get my current location.
            Geolocator myGeolocator = new Geolocator();
            Geoposition myGeoposition = await myGeolocator.GetGeopositionAsync();
            myGeocoordinate = myGeoposition.Coordinate;

            //wayPoints.Add(new GeoCoordinate(myGeocoordinate.Latitude, myGeocoordinate.Longitude));

            GeoCoordinate myGeoCoordinatex = CoordinateConverter.ConvertGeocoordinate(myGeocoordinate);

            // Make my current location the center of the Map.
            this.mapMain.Center = myGeoCoordinatex;
            this.mapMain.ZoomLevel = 16;

            // Create a MapOverlay to contain the circle.
            MapOverlay myCurentLocationOverlay = MarkerDraw.DrawCurrentMapMarker(myGeoCoordinatex);

            // Create a MapLayer to contain the MapOverlay.
            MapLayer myLocationLayer = new MapLayer();
            myLocationLayer.Add(myCurentLocationOverlay);

            // Add the MapLayer to the Map.
            mapMain.Layers.Add(myLocationLayer);

            return myGeoCoordinatex;
        }

        private void btnZoomIn_Click(object sender, RoutedEventArgs e)
        {
            try
            {
                mapMain.ZoomLevel = mapMain.ZoomLevel + 1;
            }
            catch (Exception)
            {

                throw;
            }
        }

        private void btnZoomOut_Click(object sender, RoutedEventArgs e)
        {
            if (mapMain.ZoomLevel > 1)
            {
                mapMain.ZoomLevel = mapMain.ZoomLevel - 1;
            }

        }
        #endregion

        public async void getDrivers()
        {
            myGeoCoordinate = await ShowMyCurrentLocationOnTheMap();
            
            mainMapLayer = new MapLayer();
            var result = await Request.RequestToServer.sendGetRequest("drivers/" 
                + myGeoCoordinate.Latitude.ToString().Trim() + "/" + myGeoCoordinate.Longitude.ToString().Trim());

            JObject jsonObject = JObject.Parse(result);

            string error = jsonObject.Value<string>("error").Trim();

            //var xlong = jsonObject.SelectToken("itineraries");
            JArray jsonVal = (JArray)jsonObject.SelectToken("drivers");
            //Convert json to object
            root = JsonConvert.DeserializeObject<DriverRootObject>(result);

            var x = root.drivers.First();
            GlobalData.calldriver = x.driver_id.ToString().Trim();

            foreach (Global.Driver i in root.drivers) //
            {
                
                Global.GlobalData.driverList.Add(new Driver2
                {
                    
                    driver_id = i.driver_id,
                    driver_lat = i.driver_lat,
                    driver_long = i.driver_long,
                    status = i.status,
                    
                    email = i.email,
                    fullname = i.fullname,
                    phone = i.phone,
                    personalID = i.personalID,
                    personalID_img = ImageConvert.convertBase64ToImage(i.personalID_img),                   
                    driver_avatar = ImageConvert.convertBase64ToImage(i.driver_avatar),
                    average_rating = i.average_rating
                });
                MapOverlay overlay = new MapOverlay();
                overlay = MarkerDraw.DrawDriverMarker(new GeoCoordinate(Convert.ToDouble(i.driver_lat),
                    Convert.ToDouble(i.driver_long)), Global.GlobalData.driverList.Last());
                //chua su dung
                //listMainMapOvelay.Add(overlay);

                mainMapLayer.Add(overlay);
            }
            mapMain.Layers.Add(mainMapLayer);
            longlistItineraries.ItemsSource = Global.GlobalData.driverList;
        }

        private void longlistItineraries_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            Driver2 selectedItem = (Driver2)longlistItineraries.SelectedItem;
            MessageBox.Show("ss: " + selectedItem.driver_id);
            //luu tru tam thoi
            Global.GlobalData.selectedDriver = selectedItem;
            //navigate sang details
            NavigationService.Navigate(new Uri("/Customer/CallDriver.xaml", UriKind.Relative));
        }

        #region AppbarMenu
        private void menuPostItinerary_Click(object sender, EventArgs e)
        {
            NavigationService.Navigate(new Uri("/Customer/PostItinerary.xaml", UriKind.Relative));
        }

        private void menuManage_Click(object sender, EventArgs e)
        {
            NavigationService.Navigate(new Uri("/Customer/CustomerItineraryManagement.xaml", UriKind.Relative));
        }

        private void menuMainmap_Click(object sender, EventArgs e)
        {
            NavigationService.Navigate(new Uri("/Customer/CustomerMainMap.xaml", UriKind.Relative));
        }

        private void menuAccountInfo_Click(object sender, EventArgs e)
        {
            NavigationService.Navigate(new Uri("/Customer/CustomerInfo.xaml", UriKind.RelativeOrAbsolute));
        }

        private void menuAboutUs_Click(object sender, EventArgs e)
        {
            NavigationService.Navigate(new Uri("/AboutUs.xaml", UriKind.RelativeOrAbsolute));
        }

        private void menuLogOut_Click(object sender, EventArgs e)
        {
            //xoa csdl luu tru ve driver
            Logout.deleteDriverInfoBeforeLogout();
            NavigationService.Navigate(new Uri("/LoginPage.xaml", UriKind.RelativeOrAbsolute));
        }

        private void menuSearch_Click(object sender, EventArgs e)
        {
            //display search
            StackPanel panel = new StackPanel();

            TextBox txtbStart = new TextBox();
            TextBlock b1 = new TextBlock(); b1.Text = "Password: ";

            TextBox txtbEnd = new TextBox();
            TextBlock b2 = new TextBlock(); b1.Text = "Password: ";

            Button btnAdvanceSearch = new Button(); btnAdvanceSearch.Content = "Advance Search";
            btnAdvanceSearch.Click += btnAdvanceSearch_Click;

            panel.Children.Add(b1);
            panel.Children.Add(txtbStart);
            panel.Children.Add(b2);
            panel.Children.Add(txtbEnd);
            panel.Children.Add(btnAdvanceSearch);

            CustomMessageBox messageBox = new CustomMessageBox()
            {
                //set the properties
                Caption = "Search",
                Message = "",

                LeftButtonContent = "Find",
                RightButtonContent = "Cancel"
            };

            messageBox.Content = panel;
            //messageBox.Content = b2;

            //Add the dismissed event handler
            messageBox.Dismissed += (s1, e1) =>
            {
                switch (e1.Result)
                {
                    case CustomMessageBoxResult.LeftButton:
                        //add the task you wish to perform when user clicks on yes button here
                        //goi ham search
                        //
                        var result = Request.RequestToServer.sendGetRequest("");


                        break;
                    case CustomMessageBoxResult.RightButton:
                        //add the task you wish to perform when user clicks on no button here

                        break;
                    case CustomMessageBoxResult.None:
                        // Do something.
                        break;
                    default:
                        break;
                }
            };

            //add the show method
            messageBox.Show();
        }
        #endregion


        private void btnAdvanceSearch_Click(object sender, RoutedEventArgs e)
        {
            NavigationService.Navigate(new Uri("/AdvanceSearch.xamll", UriKind.RelativeOrAbsolute));
        }

        private async void menuCallDriver_Click(object sender, EventArgs e)
        {
            geoQ.GeoCoordinate = myGeoCoordinate;
            geoQ.QueryAsync();
            Dictionary<string, string> postData = new Dictionary<string, string>();
            postData.Add("start_address", start_point);
            postData.Add("start_address_lat", myGeoCoordinate.Latitude.ToString().Trim());
            postData.Add("start_address_long", myGeoCoordinate.Longitude.ToString().Trim());
            postData.Add("end_address", "none");
            postData.Add("end_address_lat", "-1");
            postData.Add("end_address_long", "-1");
            postData.Add("driver_id", Global.GlobalData.calldriver.ToString().Trim());
            //postData.Add("time_start", "-1"); //now 
            string date2 = DateTime.Now.Year + "-" + DateTime.Now.Month + "-" + DateTime.Now.Day;
            string time2 = DateTime.Now.Hour + ":" + DateTime.Now.Minute + ":00";
            postData.Add("time_start", date2.Trim() + " " + time2.Trim());
            postData.Add("description", "call driver itinerary");
            postData.Add("distance", "-1");
            postData.Add("status", GlobalData.ITINERARY_STATUS_ONGOING.ToString());

            HttpFormUrlEncodedContent content =
                new HttpFormUrlEncodedContent(postData);
            //tao 1 itinerary ongoing
            var result = await RequestToServer.sendPostRequest("calldriveritinerary", content);

            JObject jsonObject = JObject.Parse(result);

            if (jsonObject.Value<bool>("error"))
            {
                MessageBox.Show("");
            }
            else
            {
                //set selected itinerary
                RootObject root = JsonConvert.DeserializeObject<RootObject>(result);
                foreach (Itinerary i in root.itineraries)
                {
                    Itinerary2 i2 = new Itinerary2
                    {
                        itinerary_id = i.itinerary_id,
                        driver_id = i.driver_id,
                        customer_id = Convert.ToInt32(i.customer_id),
                        start_address = i.start_address,
                        start_address_lat = i.start_address_lat,
                        start_address_long = i.start_address_long,
                        end_address = i.end_address,
                        end_address_lat = i.end_address_lat,
                        end_address_long = i.end_address_long,
                        distance = i.distance,
                        description = i.description,
                        status = i.status,
                        created_at = i.created_at,
                        time_start = i.time_start,
                        //convert base64 to image
                        //average_rating = i.average_rating

                        
                    };

                    GlobalData.selectedItinerary = i2;
                }
                Dispatcher.BeginInvoke(() =>
                {
                    string driver_id = "D" + GlobalData.calldriver;

                    //message = customer_id, itinerary_id, 
                    string message = "C" + GlobalData.user_id + "," + GlobalData.selectedItinerary.itinerary_id;
                    HubProxy.Invoke("SendPos2", driver_id, message);

                    NavigationService.Navigate(new Uri("/Customer/CustomerItineraryDetails.xaml", UriKind.RelativeOrAbsolute));
                });
            }
            
        }

        public async void createItinerary()
        {
            //send info to server
            Dictionary<string, string> postData = new Dictionary<string, string>();
            postData.Add("start_address", "none");
            postData.Add("start_address_lat", myGeoCoordinate.Latitude.ToString().Trim());
            postData.Add("start_address_long", myGeoCoordinate.Longitude.ToString().Trim());
            postData.Add("end_address", "none");
            postData.Add("end_address_lat", "-1");
            postData.Add("end_address_long", "-1");
            postData.Add("driver_id", Global.GlobalData.calldriver.ToString().Trim());
            //postData.Add("time_start", "-1"); //now 
            string date2 = DateTime.Now.Year + "-" + DateTime.Now.Month + "-" + DateTime.Now.Day;
            string time2 = DateTime.Now.Hour + ":" + DateTime.Now.Minute + ":00";
            postData.Add("time_start", date2.Trim() + " " + time2.Trim());
            postData.Add("description", "call driver itinerary");
            postData.Add("distance", "-1");
            postData.Add("status", GlobalData.ITINERARY_STATUS_ONGOING.ToString());

            HttpFormUrlEncodedContent content =
                new HttpFormUrlEncodedContent(postData);
            //tao 1 itinerary ongoing
            var result = await RequestToServer.sendPostRequest("itinerary", content);

            JObject jsonObject = JObject.Parse(result);

            if (jsonObject.Value<bool>("error"))
            {
                MessageBox.Show(jsonObject.Value<string>("message"));
            }
            else
            {
                MessageBox.Show(jsonObject.Value<string>("message"));
                //back to trang dau tien
                NavigationService.Navigate(new Uri("/Customer/CustomerItineraryManagement.xaml", UriKind.RelativeOrAbsolute));
            }        
        }

    }
}

// check neu customer status  < 3  va isDisplayMessageBox = false  ==> hien thi messagebox
// neu ko thi ko hien thi
/*if (GlobalData.customer_status < GlobalData.USER_ACCEPT_UPDATED_PROFILE && GlobalData.isDisplayMessageBox == false)
{
    MessageBoxResult result =
    MessageBox.Show("You need to update your information to use this app!",
   "Update Account", MessageBoxButton.OKCancel);

    if (result == MessageBoxResult.OK)
    {
        GlobalData.isDisplayMessageBox = true;
        NavigationService.Navigate(new Uri("/AccountInfo.xaml", UriKind.Relative));
    }
    else
    {
        GlobalData.isDisplayMessageBox = true;
    }
}*/