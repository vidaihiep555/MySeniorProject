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

namespace UberRiding.Customer
{
    public partial class CustomerMainMap : PhoneApplicationPage
    {
        MapLayer mainMapLayer = new MapLayer();
        //List<MapOverlay> listMainMapOvelay = new List<MapOverlay>();
        DriverRootObject root = null;
        List<Itinerary> itineraries = new List<Itinerary>();


        Geocoordinate myGeocoordinate = null;
        GeoCoordinate myGeoCoordinate = null;
        public CustomerMainMap()
        {
            InitializeComponent();
        }

        protected override void OnNavigatedTo(NavigationEventArgs e)
        {
            base.OnNavigatedTo(e);
            InitCurrentLocationInfo();
            getDrivers();
        }

        public async void InitCurrentLocationInfo()
        {
            Task<GeoCoordinate> x = ShowMyCurrentLocationOnTheMap();
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
            MapOverlay myCurentLocationOverlay = MarkerDraw.DrawCurrentMapMarker(myGeoCoordinate);

            // Create a MapLayer to contain the MapOverlay.
            MapLayer myLocationLayer = new MapLayer();
            myLocationLayer.Add(myCurentLocationOverlay);

            // Add the MapLayer to the Map.
            mapMain.Layers.Add(myLocationLayer);

            return myGeoCoordinate;
        }

        public async void getDrivers()
        {
            mainMapLayer = new MapLayer();
            var result = await Request.RequestToServer.sendGetRequest("drivers");

            JObject jsonObject = JObject.Parse(result);

            string error = jsonObject.Value<string>("error").Trim();

            //var xlong = jsonObject.SelectToken("itineraries");
            JArray jsonVal = (JArray)jsonObject.SelectToken("drivers");
            //Convert json to object
            root = JsonConvert.DeserializeObject<DriverRootObject>(result);

            foreach (Global.Driver i in root.drivers)
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
                    //personalID_img = ImageConvert.convertBase64ToImage(i.personalID_img),                   
                    //driver_avatar = ImageConvert.convertBase64ToImage(i.driver_avatar),
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

        private void menuPostItinerary_Click(object sender, EventArgs e)
        {
            NavigationService.Navigate(new Uri("/Customer/PostItinerary.xaml", UriKind.Relative));
        }

        private void menuManage_Click(object sender, EventArgs e)
        {
            NavigationService.Navigate(new Uri("/Customer/CustomerItineraryManagement.xaml", UriKind.Relative));
        }
    }
}