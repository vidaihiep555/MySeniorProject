using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Navigation;
using Microsoft.Phone.Controls;
using Microsoft.Phone.Shell;
using UberRiding.Global;
using Newtonsoft.Json;
using Microsoft.AspNet.SignalR.Client;
using System.Net.Http;

namespace UberRiding.Customer
{
    public partial class CustomerItineraryManagement : PhoneApplicationPage
    {
        ItineraryList itinearyCreatedList = new ItineraryList();
        ItineraryList itinearyAcceptedList = new ItineraryList();
        ItineraryList itinearyOnGoingList = new ItineraryList();
        ItineraryList itinearyFinishedList = new ItineraryList();

        public CustomerItineraryManagement()
        {
            InitializeComponent();
        }

        protected override void OnNavigatedTo(NavigationEventArgs e)
        {
            base.OnNavigatedTo(e);
            getItinerariesOfCustomer();

        }     

        public async void getItinerariesOfCustomer()
        {
            //send get request
            string result = null;
            result = await Request.RequestToServer.sendGetRequest("itineraries/customer/status");
            RootObject root = JsonConvert.DeserializeObject<RootObject>(result);
            //xu ly json
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
                    driver_avatar = ImageConvert.convertBase64ToImage(i.driver_avatar),
                    //convert base64 to image
                    //average_rating = i.average_rating
                };
                if (i2.status == GlobalData.ITINERARY_STATUS_CREATED)
                {
                    itinearyCreatedList.Add(i2);
                }
                else if (i2.status == GlobalData.ITINERARY_STATUS_ACCEPTED)
                {
                    itinearyAcceptedList.Add(i2);
                }
                else if (i2.status == GlobalData.ITINERARY_STATUS_ONGOING)
                {
                    itinearyOnGoingList.Add(i2);
                }
                else if (i2.status == GlobalData.ITINERARY_STATUS_FINISHED)
                {
                    itinearyFinishedList.Add(i2);
                }
                else
                {
                    //null
                }
            }

            longlistItinerariesCreated.ItemsSource = itinearyCreatedList;
            longlistItinerariesAccepted.ItemsSource = itinearyAcceptedList;
            longlistItinerariesOnGoing.ItemsSource = itinearyOnGoingList;
            longlistItinerariesFinished.ItemsSource = itinearyFinishedList;
        }

        private void ApplicationBarIconButton_Click(object sender, EventArgs e)
        {
            //navigate sang details
            NavigationService.Navigate(new Uri("/Driver/PostItinerary.xaml", UriKind.RelativeOrAbsolute));
        }

        #region Itinerary List
        private void longlistItinerariesCreated_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            Itinerary2 selectedItem = (Itinerary2)longlistItinerariesCreated.SelectedItem;
            MessageBox.Show("ss: " + selectedItem.itinerary_id);
            //luu tru tam thoi
            Global.GlobalData.selectedItinerary = selectedItem;
            //navigate sang details
            NavigationService.Navigate(new Uri("/Driver/DriverItineraryDetails.xaml", UriKind.RelativeOrAbsolute));
        }

        private void longlistItinerariesCustomerAccepted_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            Itinerary2 selectedItem = (Itinerary2)longlistItinerariesAccepted.SelectedItem;
            MessageBox.Show("ss: " + selectedItem.itinerary_id);
            //luu tru tam thoi
            Global.GlobalData.selectedItinerary = selectedItem;
            //navigate sang details
            NavigationService.Navigate(new Uri("/Driver/DriverItineraryDetails.xaml", UriKind.RelativeOrAbsolute));
        }

        private void longlistItinerariesDriverAccepted_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            Itinerary2 selectedItem = (Itinerary2)longlistItinerariesOnGoing.SelectedItem;
            MessageBox.Show("ss: " + selectedItem.itinerary_id);
            //luu tru tam thoi
            Global.GlobalData.selectedItinerary = selectedItem;
            //navigate sang details
            NavigationService.Navigate(new Uri("/Driver/DriverItineraryDetails.xaml", UriKind.RelativeOrAbsolute));
        }

        private void longlistItinerariesFinished_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            Itinerary2 selectedItem = (Itinerary2)longlistItinerariesFinished.SelectedItem;
            MessageBox.Show("ss: " + selectedItem.itinerary_id);
            //luu tru tam thoi
            Global.GlobalData.selectedItinerary = selectedItem;
            //navigate sang details
            NavigationService.Navigate(new Uri("/Driver/DriverItineraryDetails.xaml", UriKind.RelativeOrAbsolute));
        }
        #endregion

        #region Appbar Menu
        private void menuPostItinerary_Click(object sender, EventArgs e)
        {
            NavigationService.Navigate(new Uri("/Customer/PostItinerary.xaml", UriKind.Relative));
        }

        private void menuManage_Click(object sender, EventArgs e)
        {
            NavigationService.Navigate(new Uri("/RefreshPage.xaml", UriKind.RelativeOrAbsolute));
            //NavigationService.Navigate(new Uri("/Customer/CustomerItineraryManagement.xaml", UriKind.Relative));
        }

        private void menuMainmap_Click(object sender, EventArgs e)
        {
            NavigationService.Navigate(new Uri("/Customer/CustomerMainMap.xaml", UriKind.Relative));
        }

        private void menuAccountInfo_Click(object sender, EventArgs e)
        {
            NavigationService.Navigate(new Uri("/AccountInfo.xaml", UriKind.RelativeOrAbsolute));
        }

        private void menuAboutUs_Click(object sender, EventArgs e)
        {
            NavigationService.Navigate(new Uri("/AboutUs.xaml", UriKind.RelativeOrAbsolute));
        }

        private void menuLogOut_Click(object sender, EventArgs e)
        {
            //xoa csdl luu tru ve driver
            Logout.deleteDriverInfoBeforeLogout();
            NavigationService.Navigate(new Uri("/LoginPage.xaml", UriKind.RelativeOrAbsolute));
        }

        private void menuSearch_Click(object sender, EventArgs e)
        {

        }
        #endregion
    }
}