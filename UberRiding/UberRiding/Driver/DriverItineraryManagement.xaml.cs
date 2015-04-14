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

namespace UberRiding.Driver
{
    public partial class DriverItineraryManagement : PhoneApplicationPage
    {
        //ItineraryList itinearyCreatedList = new ItineraryList();
        ItineraryList itinearyCustomerAcceptedList = new ItineraryList();
        ItineraryList itinearyDriverAcceptedList = new ItineraryList();
        ItineraryList itinearyFinishedList = new ItineraryList();

        public DriverItineraryManagement()
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
            result = await Request.RequestToServer.sendGetRequest("itineraries/driver/status");
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
                    time = i.time,
                    //convert base64 to image
                    //average_rating = i.average_rating
                };
                //itinearyList.Add(i2);
                if (i2.status == 2)
                {
                    itinearyCustomerAcceptedList.Add(i2);
                }
                else if (i2.status == 3)
                {
                    itinearyDriverAcceptedList.Add(i2);
                }
                else if (i2.status == 4)
                {
                    itinearyFinishedList.Add(i2);
                }
                else
                {
                    //null
                }
            }
            //binding vao list

            //longlistItineraries.ItemsSource = itinearyList;
            longlistItinerariesCustomerAccepted.ItemsSource = itinearyCustomerAcceptedList;
            longlistItinerariesDriverAccepted.ItemsSource = itinearyDriverAcceptedList;
            longlistItinerariesFinished.ItemsSource = itinearyFinishedList;
        }

        private void ApplicationBarIconButton_Click(object sender, EventArgs e)
        {
            //navigate sang details
            NavigationService.Navigate(new Uri("/Driver/PostItinerary.xaml", UriKind.RelativeOrAbsolute));
        }

        private void menuCustomer_Click(object sender, EventArgs e)
        {
            //navigate sang details
            NavigationService.Navigate(new Uri("/Customer/MainMap.xaml", UriKind.RelativeOrAbsolute));
        }

        private void longlistItinerariesCustomerAccepted_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            Itinerary2 selectedItem = (Itinerary2)longlistItinerariesCustomerAccepted.SelectedItem;
            MessageBox.Show("ss: " + selectedItem.itinerary_id);
            //luu tru tam thoi
            Global.GlobalData.selectedItinerary = selectedItem;
            //navigate sang details
            NavigationService.Navigate(new Uri("/Customer/CustomerItineraryDetails.xaml", UriKind.RelativeOrAbsolute));
        }

        private void longlistItinerariesDriverAccepted_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            Itinerary2 selectedItem = (Itinerary2)longlistItinerariesDriverAccepted.SelectedItem;
            MessageBox.Show("ss: " + selectedItem.itinerary_id);
            //luu tru tam thoi
            Global.GlobalData.selectedItinerary = selectedItem;
            //navigate sang details
            NavigationService.Navigate(new Uri("/Customer/CustomerItineraryDetails.xaml", UriKind.RelativeOrAbsolute));
        }

        private void longlistItinerariesFinished_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            Itinerary2 selectedItem = (Itinerary2)longlistItinerariesFinished.SelectedItem;
            MessageBox.Show("ss: " + selectedItem.itinerary_id);
            //luu tru tam thoi
            Global.GlobalData.selectedItinerary = selectedItem;
            //navigate sang details
            NavigationService.Navigate(new Uri("/Customer/CustomerItineraryDetails.xaml", UriKind.RelativeOrAbsolute));
        }
    }
}