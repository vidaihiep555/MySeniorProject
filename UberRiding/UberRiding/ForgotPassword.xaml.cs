using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Navigation;
using Microsoft.Phone.Controls;
using Microsoft.Phone.Shell;
using UberRiding.Request;

namespace UberRiding
{
    public partial class ForgotPassword : PhoneApplicationPage
    {
        public ForgotPassword()
        {
            InitializeComponent();
        }

        private async void btnForgotPass_Click(object sender, RoutedEventArgs e)
        {
            //send request to server
            var result = await RequestToServer.sendGetRequest("forgotpassword/" + txbEmail.Text.Trim());

            //navigate to login
            NavigationService.Navigate(new Uri("Login.xaml", UriKind.RelativeOrAbsolute));
        }
    }
}