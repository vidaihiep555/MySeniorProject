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
using System.Collections.ObjectModel;

namespace UberRiding.Customer
{
    public partial class CustomerReport : PhoneApplicationPage
    {
        private ObservableCollection<LineChart> driverItineraryData = new ObservableCollection<LineChart>();
        private ObservableCollection<LineChart> driverMoneyData = new ObservableCollection<LineChart>();

        ItineraryList itinearyFinishedList = new ItineraryList();

        public CustomerReport()
        {
            InitializeComponent();

            getCustomerMoneyData();

            getCustomerItineraryData();
            //get history list
            getFinishedItinerariesOfCustomer();
        }

        public async void getFinishedItinerariesOfCustomer()
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
                    time = i.time,
                    //convert base64 to image
                    //average_rating = i.average_rating
                };
                //itinearyList.Add(i2);
                if (i2.status == GlobalData.ITINERARY_STATUS_FINISHED)
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
            
            longlistItinerariesFinished.ItemsSource = itinearyFinishedList;
        }

        //get customer money chart 
        public async void getCustomerMoneyData()
        {
            var result = await Request.RequestToServer.sendGetRequest("statistic_customer/total_money");

            RootStat root = JsonConvert.DeserializeObject<RootStat>(result);

            foreach (Stat s in root.stats)
            {
                driverMoneyData.Add(new LineChart() { label = s.month, val1 = s.number });
            }

            moneyChart.DataSource = driverMoneyData;

        }

        //get customer itinerary chart 
        public async void getCustomerItineraryData()
        {
            var result = await Request.RequestToServer.sendGetRequest("statistic_customer/itinerary");

            RootStat root = JsonConvert.DeserializeObject<RootStat>(result);

            foreach (Stat s in root.stats)
            {
                driverItineraryData.Add(new LineChart() { label = s.month, val1 = s.number });
            }

            itineraryChart.DataSource = driverItineraryData;
        }
    }
}