using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Navigation;
using Microsoft.Phone.Controls;
using Microsoft.Phone.Shell;
using System.Device.Location;
using UberRiding.Global;
using Windows.Web.Http;
using Newtonsoft.Json.Linq;
using System.Threading.Tasks;
using System.Windows.Input;
using Microsoft.Phone.Maps.Services;
using Microsoft.Phone.Maps.Controls;
using Windows.Devices.Geolocation;
using Microsoft.AspNet.SignalR.Client;
using System.Net.Http;
using UberRiding.Request;

namespace UberRiding.Customer
{
    public partial class CustomerItineraryDetails : PhoneApplicationPage
    {
        MapOverlay startPointOverlay = new MapOverlay();
        MapOverlay endPointOverlay = new MapOverlay();



        //marker for tracking
        public static MapOverlay driverOverlay = new MapOverlay();


        public static MapLayer mapLayer = new MapLayer();
        //Geocoordinate myGeocoordinate = null;
        //GeoCoordinate myGeoCoordinate = null;
        ReverseGeocodeQuery geoQ = null;

        RouteQuery routeQuery = null;
        List<GeoCoordinate> wayPoints = new List<GeoCoordinate>();

        //string nameOfTxtbox = "Start";
        Geocoordinate myGeocoordinate = null;
        GeoCoordinate myGeoCoordinate = null;


        Geolocator myLocator = null;
        //private IHubProxy HubProxy { get; set; }
        //private HubConnection con { get; set; }



        public CustomerItineraryDetails()
        {
            InitializeComponent();

            //itinerary co end point thi ve route, ko co thi cho them chuc nang update realtime
            if (GlobalData.selectedItinerary.end_address.Equals("none"))
            {
                startPointOverlay = MarkerDraw.DrawCurrentMapMarker(new GeoCoordinate(GlobalData.selectedItinerary.start_address_lat, GlobalData.selectedItinerary.start_address_long));
                wayPoints.Add(new GeoCoordinate(GlobalData.selectedItinerary.start_address_lat, GlobalData.selectedItinerary.start_address_long));
                mapLayer.Add(startPointOverlay);
            }
            else
            {
                //draw 2 points on map
                startPointOverlay = MarkerDraw.DrawCurrentMapMarker(new GeoCoordinate(GlobalData.selectedItinerary.start_address_lat, GlobalData.selectedItinerary.start_address_long));
                wayPoints.Add(new GeoCoordinate(GlobalData.selectedItinerary.start_address_lat, GlobalData.selectedItinerary.start_address_long));
                mapLayer.Add(startPointOverlay);

                endPointOverlay = MarkerDraw.DrawCurrentMapMarker(new GeoCoordinate(GlobalData.selectedItinerary.end_address_lat, GlobalData.selectedItinerary.end_address_long));
                wayPoints.Add(new GeoCoordinate(GlobalData.selectedItinerary.end_address_lat, GlobalData.selectedItinerary.end_address_long));
                mapLayer.Add(endPointOverlay);

                //draw route
                routeQuery = new RouteQuery();
                //GeocodeQuery Mygeocodequery = null;
                routeQuery.QueryCompleted += routeQuery_QueryCompleted;
                routeQuery.TravelMode = TravelMode.Driving;
                routeQuery.RouteOptimization = RouteOptimization.MinimizeDistance;
                routeQuery.Waypoints = wayPoints;
                routeQuery.QueryAsync();
            }

            //set zoom and center point
            mapItineraryDetails.ZoomLevel = 14;
            mapItineraryDetails.Center = startPointOverlay.GeoCoordinate;

            mapItineraryDetails.Layers.Add(mapLayer);

            //show status
            //hanh trinh moi dc khoi tao
            if (GlobalData.selectedItinerary.status.Equals(Global.GlobalData.ITINERARY_STATUS_CREATED))
            {
                txtItineraryInfo.Text = "Itinerary Just Created";
                //create button update hanh trinh
                Button btnUpdate = new Button();
                btnUpdate.Content = "Update";
                btnUpdate.Click += btnUpdate_Click;
                gridInfo.Children.Add(btnUpdate);
                Grid.SetRow(btnUpdate, 5);

                //create button huy hanh trinh
                Button btnDelete = new Button();
                btnDelete.Content = "Delete";
                btnDelete.Click += btnDelete_Click;
                gridInfo.Children.Add(btnDelete);
                Grid.SetRow(btnDelete, 6);

                //chinh sua tren map
                mapItineraryDetails.Tap += mapItineraryDetails_Tap;
            }
            //hanh trinh da dc accept
            else if (GlobalData.selectedItinerary.status.Equals(Global.GlobalData.ITINERARY_STATUS_ACCEPTED))
            {
                txtItineraryInfo.Text = "Itinerary Accepted";

            }
            //hanh trinh ongoing
            else if (GlobalData.selectedItinerary.status.Equals(Global.GlobalData.ITINERARY_STATUS_ONGOING))
            {
                txtItineraryInfo.Text = "Itinerary Ongoing";


                Button btnDriverInfo = new Button();
                btnDriverInfo.Content = "Driver Info";
                btnDriverInfo.Click += btnDriverInfo_Click;
                gridInfo.Children.Add(btnDriverInfo);
                Grid.SetRow(btnDriverInfo, 5);

                Button btnFinshItinerary = new Button();
                btnFinshItinerary.Content = "Finish Itinerary";
                btnFinshItinerary.Click += btnFinshItinerary_Click;
                gridInfo.Children.Add(btnFinshItinerary);
                Grid.SetRow(btnFinshItinerary, 6);

                GlobalData.ConnectCustomerAsync();

                myLocator = new Geolocator();
                myLocator.DesiredAccuracy = PositionAccuracy.High;
                myLocator.MovementThreshold = 5;
                myLocator.ReportInterval = 500;
                myLocator.PositionChanged += myGeoLocator_PositionChanged;

            }
            //hanh trinh da ket thuc
            else if (GlobalData.selectedItinerary.status.Equals(Global.GlobalData.ITINERARY_STATUS_FINISHED))
            {
                txtItineraryInfo.Text = "Itinerary Finished";

                //tao nut ket thuc


            }

            //set text 2 points
            txtboxStart.Text = GlobalData.selectedItinerary.start_address;
            txtboxEnd.Text = GlobalData.selectedItinerary.end_address;

            //set text itinerary info

            txtbDistance.Text = GlobalData.selectedItinerary.distance.ToString();
            txtbDescription.Text = GlobalData.selectedItinerary.description;
            //txtbCost.Text = GlobalData.selectedItinerary.cost;

            //xu ly ngay thang
            string datetimeString = GlobalData.selectedItinerary.time_start.Trim();

            DateTime datetime = DatetimeConvert.convertDateTimeFromString(datetimeString);

            datePicker.Value = datetime;
            timePicker.Value = datetime;
        }

        #region signalR
        /*private async void ConnectAsync()
        {
            con = new HubConnection(ServerURI);
            con.Closed += Connection_Closed;
            con.Error += Connection_Error;
            HubProxy = con.CreateHubProxy("MyHub");
            //Handle incoming event from server: use Invoke to write to console from SignalR's thread
            HubProxy.On<string, string>("getTracking", (driver_id, message) =>
                Dispatcher.BeginInvoke(() => track(message))
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

        private void track(string message)
        {
            string[] latlng = message.Split(",".ToCharArray());
            double lat = Double.Parse(latlng[2]);
            double lng = Double.Parse(latlng[3]);

            if (driverOverlay != null)
            {
                mapLayer.Remove(driverOverlay);
            }
            driverOverlay = Global.MarkerDraw.DrawDriverMarker(new GeoCoordinate(lat, lng), new Driver2());
            mapLayer.Add(driverOverlay);
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
        }*/
        #endregion

        private void myGeoLocator_PositionChanged(Geolocator sender, PositionChangedEventArgs args1)
        {
            Deployment.Current.Dispatcher.BeginInvoke(() =>
            {
                string driver_id = "D" + GlobalData.selectedItinerary.driver_id;

                //message = customer_id, itinerary_id, 
                string message = "C" + GlobalData.user_id + "," + GlobalData.selectedItinerary.itinerary_id + "," + args1.Position.Coordinate.Latitude.ToString() + "," + args1.Position.Coordinate.Longitude.ToString();

                GlobalData.HubProxy.Invoke("SendTracking", driver_id, message);
            });
        }

        async void btnFinshItinerary_Click(object sender, RoutedEventArgs e)
        {
            //update ititnerary
            Dictionary<string, string> postData = new Dictionary<string, string>();
            HttpFormUrlEncodedContent content = new HttpFormUrlEncodedContent(postData);
            var result = await RequestToServer.sendPutRequest("update_finished_itinerary/" + GlobalData.selectedItinerary.itinerary_id, content);

            JObject jsonObject = JObject.Parse(result);


            //send to
            GlobalData.calldriver = GlobalData.selectedItinerary.driver_id.ToString().Trim();

            //navigate to rating
            NavigationService.Navigate(new Uri("/Customer/RatingPage.xaml", UriKind.RelativeOrAbsolute));
        }

        void btnDriverInfo_Click(object sender, RoutedEventArgs e)
        {
            //get selected driver id variable calldriver
            GlobalData.calldriver = GlobalData.selectedItinerary.driver_id.ToString().Trim();

            NavigationService.Navigate(new Uri("/Driver/DriverAccInfo.xaml", UriKind.RelativeOrAbsolute));
        }      

        void routeQuery_QueryCompleted(object sender, QueryCompletedEventArgs<Route> e)
        {
            if (null == e.Error)
            {
                Route MyRoute = e.Result;
                MapRoute MyMapRoute = new MapRoute(MyRoute);
                mapItineraryDetails.AddRoute(MyMapRoute);
                //length of route
                //time = route / v trung binh(hang so)
                //MessageBox.Show("Distance: " + MyMapRoute.Route.LengthInMeters.ToString());
                routeQuery.Dispose();
            }
        }

        /*public async void txtboxEnd_KeyDown(object sender, KeyEventArgs e)
        {
            if (e.Key.Equals(Key.Enter))
            {
                Task<string> returnString = Request.RequestToBingMap.sendGeoCodingRequest(txtboxEnd.Text.Trim());

                //handle json return to get lat & long
                JObject jsonObject = JObject.Parse(await returnString);

                string xlong = jsonObject.SelectToken("resourceSets[0].resources[0].point.coordinates[1]").ToString().Trim();
                string xlat = jsonObject.SelectToken("resourceSets[0].resources[0].point.coordinates[0]").ToString().Trim();

                //set marker again

                if (endPointOverlay != null)
                {
                    mapLayer.Remove(endPointOverlay);
                }
                //dat pushpin
                endPointOverlay = MarkerDraw.DrawCurrentMapMarker(new GeoCoordinate(Convert.ToDouble(xlat), Convert.ToDouble(xlong)));
                // Create a MapLayer to contain the MapOverlay.
                mapLayer.Add(endPointOverlay);

                // Add the MapLayer to the Map.
                mapItineraryDetails.Layers.Remove(mapLayer);
                mapItineraryDetails.Layers.Add(mapLayer);

                this.Focus();
            }
        }

        public async void txtboxStart_KeyDown(object sender, KeyEventArgs e)
        {
            if (e.Key.Equals(Key.Enter))
            {
                Task<string> returnString = Request.RequestToBingMap.sendGeoCodingRequest(txtboxStart.Text.Trim());

                //handle json return to get lat & long
                JObject jsonObject = JObject.Parse(await returnString);

                string xlong = jsonObject.SelectToken("resourceSets[0].resources[0].point.coordinates[1]").ToString().Trim();
                string xlat = jsonObject.SelectToken("resourceSets[0].resources[0].point.coordinates[0]").ToString().Trim();

                //set marker again

                if (startPointOverlay != null)
                {
                    mapLayer.Remove(startPointOverlay);
                }
                //dat pushpin
                startPointOverlay = MarkerDraw.DrawCurrentMapMarker(new GeoCoordinate(Convert.ToDouble(xlat), Convert.ToDouble(xlong)));
                // Create a MapLayer to contain the MapOverlay.
                mapLayer.Add(startPointOverlay);

                // Add the MapLayer to the Map.
                mapItineraryDetails.Layers.Remove(mapLayer);
                mapItineraryDetails.Layers.Add(mapLayer);

                this.Focus();
            }
        }*/

        /*private async void btnReject_Click(object sender, RoutedEventArgs e)
        {
            Dictionary<string, string> postData = new Dictionary<string, string>();
            HttpFormUrlEncodedContent content =
                new HttpFormUrlEncodedContent(postData);
            var result = await Request.RequestToServer.sendPutRequest("driver_reject_itinerary/" + GlobalData.selectedItinerary.itinerary_id, content);
            JObject jsonObject = JObject.Parse(result);
            MessageBox.Show(jsonObject.Value<string>("message"));
            //do something

        }*/

        private async void btnDelete_Click(object sender, RoutedEventArgs e)
        {
            //delete itinerary
            var result = await Request.RequestToServer.sendDeleteRequest("itinerary/" + GlobalData.selectedItinerary.itinerary_id);
            JObject jsonObject = JObject.Parse(result);
            MessageBox.Show(jsonObject.Value<string>("message"));
            NavigationService.RemoveBackEntry();
            NavigationService.Navigate(new Uri("/Customer/CustomerItineraryManagement.xaml", UriKind.RelativeOrAbsolute));
        }

        private async void btnUpdate_Click(object sender, RoutedEventArgs e)
        {
            //update itinerary
            Dictionary<string, string> postData = new Dictionary<string, string>();
            postData.Add("start_address", txtboxStart.Text.Trim());
            postData.Add("end_address", txtboxEnd.Text.Trim());
            postData.Add("description", txtbDescription.Text.Trim());
            //postData.Add("cost", txtbCost.Text.Trim());
            postData.Add("distance", txtbDistance.Text.Trim());
            postData.Add("start_address_lat", startPointOverlay.GeoCoordinate.Latitude.ToString().Trim());
            postData.Add("start_address_long", startPointOverlay.GeoCoordinate.Longitude.ToString().Trim());
            postData.Add("end_address_lat", endPointOverlay.GeoCoordinate.Latitude.ToString().Trim());
            postData.Add("end_address_long", endPointOverlay.GeoCoordinate.Longitude.ToString().Trim());

            //string date = datePicker.Value.Value.ToString().Substring(0,10).Trim();
            //string time = timePicker.Value.Value.ToUniversalTime().ToString();


            string date2 = datePicker.Value.Value.Year + "-" + datePicker.Value.Value.Month + "-" + datePicker.Value.Value.Day;
            string time2 = timePicker.Value.Value.Hour + ":" + timePicker.Value.Value.Minute + ":00";

            postData.Add("time_start", date2.Trim() + " " + time2.Trim());
            //datePicker.Value.Value.
            HttpFormUrlEncodedContent content =
                new HttpFormUrlEncodedContent(postData);
            var result = await Request.RequestToServer.sendPutRequest("itinerary/" + GlobalData.selectedItinerary.itinerary_id, content);
            JObject jsonObject = JObject.Parse(result);

            if (jsonObject.Value<bool>("error"))
            {
                MessageBox.Show(jsonObject.Value<string>("message"));
            }
            else
            {
                //Global.GlobalData.isDriver = true;
                MessageBox.Show(jsonObject.Value<string>("message"));
                // refresh lai trang
                NavigationService.Navigate(new Uri("/RefreshPage.xaml", UriKind.RelativeOrAbsolute));
            }
        }

        private void mapItineraryDetails_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            if (endPointOverlay != null)
            {
                mapLayer.Remove(endPointOverlay);
            }
            GeoCoordinate asd = this.mapItineraryDetails.ConvertViewportPointToGeoCoordinate(e.GetPosition(this.mapItineraryDetails));
            //MessageBox.Show("lat: " + asd.Latitude + "; long: " + asd.Longitude);

            //dat pushpin
            endPointOverlay = MarkerDraw.DrawCurrentMapMarker(asd);
            // Create a MapLayer to contain the MapOverlay.
            mapLayer.Add(endPointOverlay);

            // Add the MapLayer to the Map.
            mapItineraryDetails.Layers.Remove(mapLayer);
            mapItineraryDetails.Layers.Add(mapLayer);

            //mapPostItinerary.Layers.Remove()
            //hien thi thong tin diem den tren textbox
            geoQ.GeoCoordinate = asd;

            geoQ.QueryAsync();
        }

        #region Appbar Menu
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
            NavigationService.Navigate(new Uri("/AccountInfo.xaml", UriKind.RelativeOrAbsolute));
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
        #endregion

        private void btnZoomIn_Click(object sender, RoutedEventArgs e)
        {
            try
            {
                mapItineraryDetails.ZoomLevel = mapItineraryDetails.ZoomLevel + 1;
            }
            catch (Exception)
            {
            }
        }

        private void btnZoomOut_Click(object sender, RoutedEventArgs e)
        {
            if (mapItineraryDetails.ZoomLevel > 1)
            {
                mapItineraryDetails.ZoomLevel = mapItineraryDetails.ZoomLevel - 1;
            }
        }
    }
}