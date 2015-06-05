using Microsoft.AspNet.SignalR.Client;
using Microsoft.Phone.Controls;
using Microsoft.Phone.Scheduler;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Media.Imaging;
using System.Windows.Threading;
using UberRiding.Request;
using Windows.Web.Http;

namespace UberRiding.Global
{
    class GlobalData
    {
        //user


        //customer


        //driver
        public static PeriodicTask periodicTask;
        ResourceIntensiveTask resourceIntensiveTask;
        public static string periodicTaskName = "PeriodicAgent";
        public static string resourceIntensiveTaskName = "ResourceIntensiveAgent";
        public static bool agentsAreEnabled = true;

        public static void StartPeriodicAgent()
        {

            agentsAreEnabled = true;
            periodicTask = ScheduledActionService.Find(periodicTaskName) as PeriodicTask;
            if (periodicTask != null)
            {
                RemoveAgent(periodicTaskName);
            }
            periodicTask = new PeriodicTask(periodicTaskName);
            periodicTask = new PeriodicTask(periodicTaskName);
            periodicTask.Description = "This demonstrates a periodic task.";
            // Place the call to add in a try block in case the user has disabled agents.
            try
            {
                ScheduledActionService.Add(periodicTask);
                //PeriodicStackPanel.DataContext = periodicTask;
                // If debugging is enabled , use LaunchForTest to launch the agent in one minutes
                //#if (DEBUG_AGENT)
                ScheduledActionService.LaunchForTest(periodicTaskName, TimeSpan.FromSeconds(5));
                //#endif
            }
            catch (InvalidOperationException exception)
            {
                if (exception.Message.Contains("BNS Error: The action is disabled"))
                {
                    MessageBox.Show("Bakcground agents for this application have been disabled by the user.");
                    agentsAreEnabled = false;
                    //PeriodicCheckBox.IsChecked = false;
                }
                if (exception.Message.Contains("BNS Error: The maximum number of ScheduledActions of this type have already been added."))
                {
                    // No user action required.
                }
                //PeriodicCheckBox.IsChecked = false;
            }
            catch (SchedulerServiceException)
            {
                // PeriodicCheckBox.IsChecked = false;
            }
        }

        public static void RemoveAgent(string name)
        {
            try
            {
                ScheduledActionService.Remove(name);
            }
            catch (Exception)
            {
            }
        }

        public static IHubProxy HubProxy { get; set; }
        public static string ServerURI = "http://52.25.218.73:8080/signalr";
        public static HubConnection con { get; set; }

        public static async void ConnectDriverAsync()
        {
            GlobalData.con = new HubConnection(GlobalData.ServerURI);
            GlobalData.con.Closed += GlobalData.Connection_Closed;
            GlobalData.con.Error += GlobalData.Connection_Error;
            GlobalData.HubProxy = GlobalData.con.CreateHubProxy("MyHub");
            //Handle incoming event from server: use Invoke to write to console from SignalR's thread
            GlobalData.HubProxy.On<string, string>("getPos2", (driver_id, message) =>
                Deployment.Current.Dispatcher.BeginInvoke(() => test(message))
            );
            try
            {
                await GlobalData.con.Start();
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

            Deployment.Current.Dispatcher.BeginInvoke(() =>
            {
                string id = "D" + Global.GlobalData.user_id;
                GlobalData.HubProxy.Invoke("Connect", id);
            });


        }

        public static async void test(string message)
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

                var result = await RequestToServer.sendGetRequest("itinerary/" + x[1]);

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

                (Application.Current.RootVisual as PhoneApplicationFrame).Navigate(new Uri("/Driver/DriverItineraryDetails.xaml", UriKind.RelativeOrAbsolute));
            }
            //string[] latlng = message.Split(",".ToCharArray());
            //double lat = Double.Parse(latlng[0]);
            //double lng = Double.Parse(latlng[1]);
            //addMarkertoMap(new GeoCoordinate(lat, lng));
            //txtFireBase.Text = message;
        }

        public static void Connection_Closed()
        {
            //Deactivate chat UI; show login UI. 
        }

        public static void Connection_Error(Exception obj)
        {
            //txtFireBase.Text = "error";
        }
        
        //////////////
        
        public static ItineraryList itinearyList = new ItineraryList();
        public static Itinerary2 selectedItinerary = new Itinerary2();

        public static DriverList driverList  = new DriverList();
        public static Driver2 selectedDriver = new Driver2();

        public static VehicleList vehicleList = new VehicleList();
        public static Vehicle2 selectedVehicle = new Vehicle2();

        public static string user_id;

        public static string calldriver;

        public static bool isDriver = false;

        public static string APIkey = null;

        public static int customer_status = 0;

        public static int driver_status = 0;

        //public static bool isDriver = true;

        //public static string APIkey = "ce1fb637b7eee845c73b207d931bbc10";

        //public static int customer_status = 4;

        //public static int driver_status = 2;

        public const int ITINERARY_STATUS_CREATED = 1;
        public const int ITINERARY_STATUS_ACCEPTED = 2;
        public const int ITINERARY_STATUS_ONGOING = 3;
        public const int ITINERARY_STATUS_FINISHED = 4;

        public const int DRIVER_BUSY = 0;
        public const int DRIVER_NOT_BUSY = 1;
    }


    #region Itinerary Class
    public class Itinerary
    {
        public int itinerary_id { get; set; }
        public int driver_id { get; set; }
        public int? customer_id { get; set; }
        public string start_address { get; set; }
        public double start_address_lat { get; set; }
        public double start_address_long { get; set; }
        public string end_address { get; set; }
        public double end_address_lat { get; set; }
        public double end_address_long { get; set; }
        public string time_start { get; set; }
        public double distance { get; set; }
        public string cost { get; set; }
        public string description { get; set; }
        public int status { get; set; }
        public string created_at { get; set; }
        public string driver_license { get; set; }
        public string driver_license_img { get; set; }
        public int user_id { get; set; }
        public string email { get; set; }
        public string fullname { get; set; }
        public string phone { get; set; }
        public string personalID { get; set; }
        public string driver_avatar { get; set; }
        public string customer_avatar { get; set; }
        public int average_rating { get; set; }
    }

    public class Itinerary2
    {
        public int itinerary_id { get; set; }
        public int driver_id { get; set; }
        public int customer_id { get; set; }
        public string start_address { get; set; }
        public double start_address_lat { get; set; }
        public double start_address_long { get; set; }
        public string end_address { get; set; }
        public double end_address_lat { get; set; }
        public double end_address_long { get; set; }
        public string time_start { get; set; }
        public double distance { get; set; }
        public string cost { get; set; }
        public string description { get; set; }
        public int status { get; set; }
        public string created_at { get; set; }
        public string driver_license { get; set; }
        public string driver_license_img { get; set; }
        public int user_id { get; set; }
        public string email { get; set; }
        public string fullname { get; set; }
        public string phone { get; set; }
        public string personalID { get; set; }
        public BitmapImage driver_avatar { get; set; }
        public BitmapImage customer_avatar { get; set; }
        public int average_rating { get; set; }
    }

    public class RootObject
    {
        public bool error { get; set; }
        public List<Itinerary> itineraries { get; set; }
    }

    public class ItineraryList : List<Itinerary2>
    {

    }
    #endregion

    #region Driver Class
    public class Driver
    {
        public int driver_id { get; set; }
        public double driver_lat { get; set; }
        public double driver_long { get; set; }
        public int status { get; set; }
        public string email { get; set; }
        public string fullname { get; set; }
        public string phone { get; set; }

        public string personalID { get; set; }
        public string personalID_img { get; set; }
        public string driver_avatar { get; set; }
        public int average_rating { get; set; }
    }

    public class Driver2
    {
        public int driver_id { get; set; }
        public double driver_lat { get; set; }
        public double driver_long { get; set; }
        public int status { get; set; }
        public string email { get; set; }
        public string fullname { get; set; }
        public string phone { get; set; }
        public string personalID { get; set; }
        public BitmapImage personalID_img { get; set; }
        public BitmapImage driver_avatar { get; set; }
        public int average_rating { get; set; }
        public double distance_todriver { get; set; }
    }

    public class DriverRootObject
    {
        public bool error { get; set; }
        public List<Driver> drivers { get; set; }
    }

    public class DriverList : List<Driver2>
    {

    }
    #endregion

    #region Stat Class
    public class Stat
    {
        public string month { get; set; }
        public int number { get; set; }
    }

    public class RootStat
    {
        public bool error { get; set; }
        public List<Stat> stats { get; set; }
    }
    #endregion

    #region Vehicle Class
    public class Vehicle
    {
        public int vehicle_id { get; set; }
        public int user_id { get; set; }
        public string type { get; set; }
        public string license_plate { get; set; }
        public string reg_certificate { get; set; }
        public string license_plate_img { get; set; }
        public string vehicle_img { get; set; }
        public string motor_insurance_img { get; set; }
        public int status { get; set; }
        public string created_at { get; set; }
    }

    public class Vehicle2
    {
        public int vehicle_id { get; set; }
        public int user_id { get; set; }
        public string type { get; set; }
        public string license_plate { get; set; }
        public string reg_certificate { get; set; }
        public BitmapImage license_plate_img { get; set; }
        public BitmapImage vehicle_img { get; set; }
        public BitmapImage motor_insurance_img { get; set; }
        public int status { get; set; }
        public string created_at { get; set; }
    }

    public class RootVehicle
    {
        public bool error { get; set; }
        public List<Vehicle> vehicles { get; set; }
    }

    public class VehicleList : List<Vehicle2>
    {

    }
    #endregion

    #region Chart
    public class LineChart
    {
        public string label { get; set; }
        public double val1 { get; set; }
        public double val2 { get; set; }
        public decimal val3 { get; set; }
    }
    #endregion 
}
