using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Navigation;
using Microsoft.Phone.Controls;
using Microsoft.Phone.Shell;
using Microsoft.Phone.Maps.Services;
using Microsoft.Phone.Maps.Controls;
using System.Device.Location;
using UberRiding.Global;
using Windows.Devices.Geolocation;
using Microsoft.AspNet.SignalR.Client;
using System.Net.Http;

namespace UberRiding.Driver
{
    public partial class DriverItineraryDetails : PhoneApplicationPage
    {
        MapOverlay startPointOverlay = new MapOverlay();
        MapOverlay endPointOverlay = new MapOverlay();
        MapLayer mapLayer = new MapLayer();
        //Geocoordinate myGeocoordinate = null;
        //GeoCoordinate myGeoCoordinate = null;
        ReverseGeocodeQuery geoQ = null;

        RouteQuery routeQuery = null;
        List<GeoCoordinate> wayPoints = new List<GeoCoordinate>();
        //string nameOfTxtbox = "Start";

        MapOverlay driverOverlay = new MapOverlay();
        Geolocator myLocator = null;
        private IHubProxy HubProxy { get; set; }
        const string ServerURI = "http://52.11.206.209:8080/signalr";
        //const string ServerURI = "http://localhost:8080/signalr";
        private HubConnection con { get; set; }
        public DriverItineraryDetails()
        {
            InitializeComponent();

            //show status
            //hanh trinh moi dc khoi tao
            //...............

            //hanh trinh da dc accept
            if (GlobalData.selectedItinerary.status.Equals(Global.GlobalData.ITINERARY_STATUS_ACCEPTED))
            {
                txtItineraryInfo.Text = "Itinerary Accepted";
                /*//tao button accept va button huy customer accept
                //create button accept hanh trinh
                Button btnAccept = new Button();
                btnAccept.Content = "Chấp Nhận";
                btnAccept.Click += btnAccept_Click;
                gridInfo.Children.Add(btnAccept);
                Grid.SetRow(btnAccept, 5);

                //create button reject hanh trinh
                Button btnReject = new Button();
                btnReject.Content = "Từ Chối";
                btnReject.Click += btnReject_Click;
                gridInfo.Children.Add(btnReject);
                Grid.SetRow(btnReject, 6);*/
            }
            //hanh trinh ongoing
            else if (GlobalData.selectedItinerary.status.Equals(Global.GlobalData.ITINERARY_STATUS_ONGOING))
            {
                txtItineraryInfo.Text = "Itinerary Ongoing";
                // tracking

                Button btnTracking = new Button();
                btnTracking.Content = "Tracking";
                btnTracking.Click += btnTracking_Click;
                gridInfo.Children.Add(btnTracking);
                Grid.SetRow(btnTracking, 5);




                ConnectAsync();

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
            }

            if (!GlobalData.selectedItinerary.end_address.Equals("none"))
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
            else
            {
                
            }
            

            //set zoom and center point
            mapItineraryDetails.ZoomLevel = 14;
            mapItineraryDetails.Center = startPointOverlay.GeoCoordinate;

            mapItineraryDetails.Layers.Add(mapLayer);         

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

            //datePicker.Value = GlobalData.selectedItinerary.da
        }

        private async void ConnectAsync()
        {
            con = new HubConnection(ServerURI);
            con.Closed += Connection_Closed;
            con.Error += Connection_Error;
            HubProxy = con.CreateHubProxy("MyHub");
            //Handle incoming event from server: use Invoke to write to console from SignalR's thread
            HubProxy.On<string, string>("getPos2", (driver_id, message) =>
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
        private void myGeoLocator_PositionChanged(Geolocator sender, PositionChangedEventArgs args1)
        {
            //draw on map




            Dispatcher.BeginInvoke(() =>
            {
                string driver_id = "D" + GlobalData.calldriver;

                //message = customer_id, itinerary_id, 
                string message = "C" + GlobalData.user_id + "," + GlobalData.selectedItinerary.itinerary_id + "," + args1.Position.Coordinate.Latitude.ToString() + "," + args1.Position.Coordinate.Longitude.ToString();

                HubProxy.Invoke("SendPos2", driver_id, message);
            });
        }

        private void btnTracking_Click(object sender, RoutedEventArgs e)
        {
            throw new NotImplementedException();
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

        /*void geoQ_QueryCompleted(object sender, QueryCompletedEventArgs<IList<MapLocation>> e)
        {
            if (e.Result.Count() > 0)
            {
                string showString = e.Result[0].Information.Name;
                showString = showString + "";
                showString = showString + "" + e.Result[0].Information.Address.HouseNumber + " " + e.Result[0].Information.Address.Street;
                showString = showString + "" + e.Result[0].Information.Address.PostalCode + " " + e.Result[0].Information.Address.City;
                showString = showString + "" + e.Result[0].Information.Address.Country + " " + e.Result[0].Information.Address.CountryCode;
                //MessageBox.Show(showString);
                if (nameOfTxtbox.Equals("Start"))
                {
                    txtboxStart.Text = showString;
                    nameOfTxtbox = "End";
                }
                else
                {
                    txtboxEnd.Text = showString;
                }

            }
            mapItineraryDetails.IsEnabled = true;
        }*/

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