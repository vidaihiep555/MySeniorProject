using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Navigation;
using Microsoft.Phone.Controls;
using Microsoft.Phone.Shell;
using Newtonsoft.Json.Linq;
using UberRiding.Request;

namespace UberRiding
{
    public partial class AccountInfo : PhoneApplicationPage
    {
        public AccountInfo()
        {
            InitializeComponent();

            //xet co phai driver ko

            //send request

            
        }

        public async void getUserInfo()
        {
            var result = await RequestToServer.sendGetRequest("user/login");

            JObject jsonObject = JObject.Parse(result);

            txtbEmail.Text = jsonObject.Value<string>("email");
        }
    }
}