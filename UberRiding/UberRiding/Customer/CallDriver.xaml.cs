using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Navigation;
using Microsoft.Phone.Controls;
using Microsoft.Phone.Shell;
using Windows.Web.Http;
using Newtonsoft.Json.Linq;
using UberRiding.Request;
using Microsoft.Phone.Maps.Controls;
using System.Threading.Tasks;
using System.Device.Location;
using Windows.Devices.Geolocation;
using UberRiding.Global;
using Microsoft.Phone.Maps.Services;

namespace UberRiding.Customer
{
    public partial class CallDriver : PhoneApplicationPage
    {
        
        MapOverlay startPointOverlay = new MapOverlay();
        //MapOverlay endPointOverlay = new MapOverlay();
        MapLayer mapLayer = new MapLayer();

        Geocoordinate myGeocoordinate = null;
        GeoCoordinate myGeoCoordinate = null;
        ReverseGeocodeQuery geoQ = null;

        public CallDriver()
        {
            InitializeComponent();
            geoQ = new ReverseGeocodeQuery();
            geoQ.QueryCompleted += geoQ_QueryCompleted;
            if (geoQ.IsBusy == true)
            {
                geoQ.CancelAsync();
            }
            mapLayer = new MapLayer();
            //get current location
            InitCurrentLocationInfo();
            //draw driver len map
            MapOverlay overlay = new MapOverlay();
            overlay = MarkerDraw.DrawDriverMarker(new GeoCoordinate(Convert.ToDouble(GlobalData.selectedDriver.driver_lat),
                Convert.ToDouble(GlobalData.selectedDriver.driver_long)), Global.GlobalData.selectedDriver);
            mapLayer.Add(overlay);

            mapMain.Layers.Add(mapLayer);

            //load thong tin driver
            txtbFullname.Text = Global.GlobalData.selectedDriver.fullname;
            txtbEmail.Text = Global.GlobalData.selectedDriver.email;
            txtbPersonalID.Text = Global.GlobalData.selectedDriver.personalID;
            txtbEmail.Text = Global.GlobalData.selectedDriver.email;

            imgDriver.Source = Global.GlobalData.selectedDriver.driver_avatar;
            
        }

        public async void InitCurrentLocationInfo()
        {
            Task<GeoCoordinate> x = ShowMyCurrentLocationOnTheMap();

            geoQ.GeoCoordinate = await x;

            geoQ.QueryAsync();
        }

        void geoQ_QueryCompleted(object sender, QueryCompletedEventArgs<IList<MapLocation>> e)
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

                txtboxStart.Text = showString;
                //MessageBox.Show(showString);
                /*if (nameOfTxtbox.Equals("Start"))
                {
                    txtboxStart.Text = showString;
                    nameOfTxtbox = "End";
                }
                else
                {
                    txtboxEnd.Text = showString;
                }*/
                //txtboxStart.Text = showString;
                //return showString;

            }
            //this.Cursor = Cursors.None;
            //return "null";
            //mapPostItinerary.IsEnabled = true;
        }

        private async Task<GeoCoordinate> ShowMyCurrentLocationOnTheMap()
        {
            // Get my current location.
            Geolocator myGeolocator = new Geolocator();
            Geoposition myGeoposition = await myGeolocator.GetGeopositionAsync();
            myGeocoordinate = myGeoposition.Coordinate;

            //wayPoints.Add(new GeoCoordinate(myGeocoordinate.Latitude, myGeocoordinate.Longitude));

            myGeoCoordinate = CoordinateConverter.ConvertGeocoordinate(myGeocoordinate);

            // Make my current location the center of the Map.
            this.mapMain.Center = myGeoCoordinate;
            this.mapMain.ZoomLevel = 16;

            // Create a MapOverlay to contain the circle.
            startPointOverlay = MarkerDraw.DrawCurrentMapMarker(myGeoCoordinate);

            // Create a MapLayer to contain the MapOverlay.
            MapLayer myLocationLayer = new MapLayer();
            myLocationLayer.Add(startPointOverlay);

            // Add the MapLayer to the Map.
            mapMain.Layers.Add(myLocationLayer);

            return myGeoCoordinate;
        }

        private async void btnCallDriver_Click(object sender, RoutedEventArgs e)
        {
            //send info to server
            Dictionary<string, string> postData = new Dictionary<string, string>();
            postData.Add("start_address", txtboxStart.Text.Trim());
            postData.Add("start_address_lat", startPointOverlay.GeoCoordinate.Latitude.ToString().Trim());
            postData.Add("start_address_long", startPointOverlay.GeoCoordinate.Longitude.ToString().Trim());
            postData.Add("driver_id", Global.GlobalData.selectedDriver.driver_id.ToString().Trim());

            postData.Add("status", GlobalData.ITINERARY_STATUS_ONGOING.ToString());

            //string date = datePicker.Value.ToString();
            //string time = timePicker.Value.ToString();
            //postData.Add("time", "2011-07-07 04:04:04");
            //postData.Add("duration", txtbDistance.Text.Trim());
            HttpFormUrlEncodedContent content =
                new HttpFormUrlEncodedContent(postData);
            //tao 1 itinerary ongoing
            var result = await RequestToServer.sendPostRequest("itinerary/simple", content);

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

        private void mapItineraryDetails_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {

        }

        private void txtboxStart_KeyDown(object sender, System.Windows.Input.KeyEventArgs e)
        {

        }

        private void txtboxEnd_KeyDown(object sender, System.Windows.Input.KeyEventArgs e)
        {

        }


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
    }
}