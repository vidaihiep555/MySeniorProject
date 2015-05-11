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
        public DriverItineraryDetails()
        {
            InitializeComponent();

            //show status
            //hanh trinh moi dc khoi tao
            if (GlobalData.selectedItinerary.status.Equals(Global.GlobalData.ITINERARY_STATUS_CREATED))
            {
                txtItineraryInfo.Text = "Itinerary Just Created";
                //create button update hanh trinh
                Button btnUpdate = new Button();
                btnUpdate.Content = "Update";
                //btnUpdate.Click += btnUpdate_Click;
                gridInfo.Children.Add(btnUpdate);
                Grid.SetRow(btnUpdate, 5);

                //create button huy hanh trinh
                Button btnDelete = new Button();
                btnDelete.Content = "Delete";
                //btnDelete.Click += btnDelete_Click;
                gridInfo.Children.Add(btnDelete);
                Grid.SetRow(btnDelete, 6);

                //chinh sua tren map
            }
            //hanh trinh da dc accept
            else if (GlobalData.selectedItinerary.status.Equals(Global.GlobalData.ITINERARY_STATUS_ACCEPTED))
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
                // 
            }
            //hanh trinh da ket thuc
            else if (GlobalData.selectedItinerary.status.Equals(Global.GlobalData.ITINERARY_STATUS_FINISHED))
            {
                txtItineraryInfo.Text = "Itinerary Finished";
            }

            //draw 2 points on map
            startPointOverlay = MarkerDraw.DrawCurrentMapMarker(new GeoCoordinate(GlobalData.selectedItinerary.start_address_lat, GlobalData.selectedItinerary.start_address_long));
            wayPoints.Add(new GeoCoordinate(GlobalData.selectedItinerary.start_address_lat, GlobalData.selectedItinerary.start_address_long));
            mapLayer.Add(startPointOverlay);

            endPointOverlay = MarkerDraw.DrawCurrentMapMarker(new GeoCoordinate(GlobalData.selectedItinerary.end_address_lat, GlobalData.selectedItinerary.end_address_long));
            wayPoints.Add(new GeoCoordinate(GlobalData.selectedItinerary.end_address_lat, GlobalData.selectedItinerary.end_address_long));
            mapLayer.Add(endPointOverlay);

            //set zoom and center point
            mapItineraryDetails.ZoomLevel = 14;
            mapItineraryDetails.Center = startPointOverlay.GeoCoordinate;

            mapItineraryDetails.Layers.Add(mapLayer);

            //draw route
            routeQuery = new RouteQuery();
            //GeocodeQuery Mygeocodequery = null;
            routeQuery.QueryCompleted += routeQuery_QueryCompleted;
            routeQuery.TravelMode = TravelMode.Driving;
            routeQuery.RouteOptimization = RouteOptimization.MinimizeDistance;
            routeQuery.Waypoints = wayPoints;
            routeQuery.QueryAsync();

            //set text 2 points
            txtboxStart.Text = GlobalData.selectedItinerary.start_address;
            txtboxEnd.Text = GlobalData.selectedItinerary.end_address;

            //set text itinerary info

            txtbDistance.Text = GlobalData.selectedItinerary.distance.ToString();
            txtbDescription.Text = GlobalData.selectedItinerary.description;
            //txtbCost.Text = GlobalData.selectedItinerary.cost;

            //xu ly ngay thang
            string datetimeString = GlobalData.selectedItinerary.time.Trim();

            DateTime datetime = DatetimeConvert.convertDateTimeFromString(datetimeString);

            datePicker.Value = datetime;
            timePicker.Value = datetime;

            //datePicker.Value = GlobalData.selectedItinerary.da
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