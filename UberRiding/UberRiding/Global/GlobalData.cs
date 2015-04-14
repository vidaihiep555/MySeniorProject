using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Media.Imaging;

namespace UberRiding.Global
{
    class GlobalData
    {
        public static ItineraryList itinearyList = new ItineraryList();
        public static Itinerary2 selectedItinerary = new Itinerary2();

        public static DriverList driverList  = new DriverList();
        public static Driver2 selectedDriver = new Driver2();

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
    }

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
        public string time { get; set; }
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
        public string link_avatar { get; set; }
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
        public string time { get; set; }
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
        public BitmapImage link_avatar { get; set; }
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
    }

    public class DriverRootObject
    {
        public bool error { get; set; }
        public List<Driver> drivers { get; set; }
    }

    public class DriverList : List<Driver2>
    {

    }
}
