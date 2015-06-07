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
using UberRiding.Global;
using Microsoft.AspNet.SignalR.Client;
using System.Net.Http;

namespace UberRiding.Customer
{
    public partial class AdvancePostItinerary : PhoneApplicationPage
    {
        string start = "";
        string end = "";
        string start_lat, start_long, end_lat, end_long = "";

        //private IHubProxy HubProxy { get; set; }
        //private HubConnection con { get; set; }

        public AdvancePostItinerary()
        {
            InitializeComponent();
        }

        protected override void OnNavigatedTo(System.Windows.Navigation.NavigationEventArgs e)
        {
            base.OnNavigatedTo(e);

            GlobalData.ConnectCustomerAsync();

            if (NavigationContext.QueryString.TryGetValue("start", out start))
            {
                txtbStart.Text = start;
            }

            if (NavigationContext.QueryString.TryGetValue("end", out end))
            {
                txtbEnd.Text = end;
            }

            if (NavigationContext.QueryString.TryGetValue("s_lat", out start_lat) && NavigationContext.QueryString.TryGetValue("s_long", out start_long)
                && NavigationContext.QueryString.TryGetValue("e_lat", out end_lat) && NavigationContext.QueryString.TryGetValue("e_long", out end_long))
            {
                //OK
            }
            else
            {
                NavigationService.GoBack();
            }

        }

        #region signalR
        /*private async void ConnectAsync()
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
            //string[] latlng = message.Split(",".ToCharArray());
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

        private async void btnRegister_Click(object sender, RoutedEventArgs e)
        {
            //validate

            //send info to server
            Dictionary<string, string> postData = new Dictionary<string, string>();
            postData.Add("start_address", txtbStart.Text.Trim());
            postData.Add("end_address", txtbEnd.Text.Trim());
            postData.Add("description", txtbDescription.Text.Trim());
            //postData.Add("cost", txtbCost.Text.Trim());
            postData.Add("distance", txtbDistance.Text.Trim());
            postData.Add("start_address_lat", start_lat.Trim());
            postData.Add("start_address_long", start_long.Trim());
            postData.Add("end_address_lat", end_lat.Trim());
            postData.Add("end_address_long", end_long.Trim());

            int day = datePicker.Value.Value.Day;
            int month = datePicker.Value.Value.Month;
            int year = datePicker.Value.Value.Year;
            int hour = datePicker.Value.Value.Hour;
            int minute = datePicker.Value.Value.Minute;
            string date2 = year + "-" + month + "-" + day;
            string time2 = hour + ":" + minute + ":00";

            postData.Add("time_start", date2.Trim() + " " + time2.Trim());

            postData.Add("day", day.ToString());
            postData.Add("month", month.ToString());
            postData.Add("year", year.ToString());

            postData.Add("from_hour", hour.ToString());
            postData.Add("to_hour", (hour+3).ToString());
            //postData.Add("duration", txtbDistance.Text.Trim());
            HttpFormUrlEncodedContent content =
                new HttpFormUrlEncodedContent(postData);

            //var result = await RequestToServer.sendPostRequest("itinerary", content);
            var result = await RequestToServer.sendPostRequest("zzz", content);
            JObject jsonObject = JObject.Parse(result);


            if (jsonObject.Value<string>("error").Trim().Equals("true"))
            {
                MessageBox.Show(jsonObject.Value<string>("message"));
            }
            else
            {
                //send to driver via signalR
                string post_driver_id = jsonObject.Value<string>("driver_id");
                string itinerary_id = jsonObject.Value<string>("itinerary_id");

                Deployment.Current.Dispatcher.BeginInvoke(() =>
                {
                    string driver_id = "D" + post_driver_id;
                    string message = "C" + GlobalData.user_id + "," + itinerary_id;
                    GlobalData.HubProxy.Invoke("SendPostItinerary", driver_id, message);

                    //NavigationService.Navigate(new Uri("/Customer/CustomerItineraryDetails.xaml", UriKind.RelativeOrAbsolute));
                });

                //set alarm
                //DateTime datetime = new DateTime(year, month, day, hour, minute, 00);
                //ScheduleReminder.addScheduleReminder("name", "title", "content", datetime.AddHours(-2), datetime);

            }

            //back to trang dau tien
            NavigationService.Navigate(new Uri("/Customer/CustomerItineraryManagement.xaml", UriKind.RelativeOrAbsolute));
        }
    }
}