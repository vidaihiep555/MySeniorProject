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

namespace UberRiding.Customer
{
    public partial class RatingPage : PhoneApplicationPage
    {
        public RatingPage()
        {
            InitializeComponent();
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