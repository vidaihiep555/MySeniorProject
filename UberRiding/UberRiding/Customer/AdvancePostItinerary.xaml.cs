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

namespace UberRiding.Customer
{
    public partial class AdvancePostItinerary : PhoneApplicationPage
    {
        public AdvancePostItinerary()
        {
            InitializeComponent();
        }

        string start = "";
        string end = "";
        string start_lat, start_long, end_lat, end_long = "";

        protected override void OnNavigatedTo(System.Windows.Navigation.NavigationEventArgs e)
        {
            base.OnNavigatedTo(e);

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

            postData.Add("hour", hour.ToString());
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



                //set alarm
                DateTime datetime = new DateTime(year, month, day, hour, minute, 00);
                ScheduleReminder.addScheduleReminder("name", "title", "content", datetime.AddHours(-2), datetime);

            }

            //back to trang dau tien
            NavigationService.Navigate(new Uri("/Customer/CustomerItineraryManagement.xaml", UriKind.RelativeOrAbsolute));
        }
    }
}