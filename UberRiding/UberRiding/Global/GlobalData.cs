using Microsoft.AspNet.SignalR.Client;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Media.Imaging;
using System.Windows.Threading;

namespace UberRiding.Global
{
    class GlobalData
    {
        //user


        //customer


        //driver

        public static IHubProxy HubProxy { get; set; }
        //public static const string ServerURI = "http://52.25.218.73:8080/signalr";
        public static HubConnection con { get; set; }

        public static async void test(string message)
        {

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
