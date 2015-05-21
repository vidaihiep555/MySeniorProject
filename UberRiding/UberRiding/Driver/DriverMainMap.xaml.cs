using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Navigation;
using Microsoft.Phone.Controls;
using Microsoft.Phone.Shell;
using Microsoft.AspNet.SignalR.Client;
using System.Net.Http;
using UberRiding.Global;
using Windows.Web.Http;
using UberRiding.Request;
using Newtonsoft.Json;

namespace UberRiding.Driver
{
    public partial class DriverMainMap : PhoneApplicationPage
    {
        private IHubProxy HubProxy { get; set; }
        const string ServerURI = "http://52.11.206.209:8080/signalr";
        //const string ServerURI = "http://localhost:8080/signalr";
        private HubConnection con { get; set; }
        public DriverMainMap()
        {
            InitializeComponent();

            ConnectAsync();
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
                string id = "D" + Global.GlobalData.user_id;
                HubProxy.Invoke("Connect", id);
            });

            
        }

        private async void test(string message)
        {
            //show message box
            var resultMessageBox = MessageBox.Show("Do it now");

            if (resultMessageBox == MessageBoxResult.OK)
            {
                string[] x = message.Split(',');
                //send message

                Dictionary<string, string> updateData = new Dictionary<string, string>();
                updateData.Add("busy_status", GlobalData.DRIVER_BUSY.ToString());
                HttpFormUrlEncodedContent updateDataContent = new HttpFormUrlEncodedContent(updateData);
                var update = await RequestToServer.sendPutRequest("driverbusy", updateDataContent);

                var result = await RequestToServer.sendGetRequest("itinerary/" + x[2]);

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

                NavigationService.Navigate(new Uri("/Driver/DriverItineraryDetails.xaml", UriKind.RelativeOrAbsolute));
            }
            //string[] latlng = message.Split(",".ToCharArray());
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
    }
}