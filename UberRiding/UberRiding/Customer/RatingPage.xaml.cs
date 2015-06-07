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
using UberRiding.Request;
using UberRiding.Global;
using Newtonsoft.Json.Linq;

namespace UberRiding.Customer
{
    public partial class RatingPage : PhoneApplicationPage
    {
        public RatingPage()
        {
            InitializeComponent();

            //Global.GlobalData.sel
            customerGetUserInfo();
            
        }

        public async void customerGetUserInfo()
        {
            var result = await RequestToServer.sendGetRequest("customergetdriver/" + GlobalData.calldriver);

            JObject jsonObject = JObject.Parse(result);

            txtbDriverName.Text = jsonObject.Value<string>("fullname");
            //set Image
            imgAvatar.Source = ImageConvert.convertBase64ToImage(jsonObject.Value<string>("driver_avatar").Trim());
        }

        private async void btnSend_Click(object sender, RoutedEventArgs e)
        {
            //send rating
            Dictionary<string, string> postData = new Dictionary<string, string>();
            postData.Add("rating", ratingDriver.Value.ToString());
            postData.Add("driver_id", Global.GlobalData.calldriver);
            HttpFormUrlEncodedContent content = new HttpFormUrlEncodedContent(postData);

            var result = await RequestToServer.sendPostRequest("rating", content);

            NavigationService.Navigate(new Uri("/Customer/CustomerMainMap.xaml", UriKind.Relative));
        }
    }
}