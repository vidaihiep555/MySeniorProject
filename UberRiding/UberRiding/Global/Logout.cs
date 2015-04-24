using System;
using System.Collections.Generic;
using System.IO.IsolatedStorage;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace UberRiding.Global
{
    class Logout
    {
        public static void deleteDriverInfoBeforeLogout()
        {
            IsolatedStorageSettings.ApplicationSettings["isLogin"] = null;
            IsolatedStorageSettings.ApplicationSettings["APIkey"] = null;
            IsolatedStorageSettings.ApplicationSettings["isDriver"] = null;
            IsolatedStorageSettings.ApplicationSettings["customer_status"] = null;
            IsolatedStorageSettings.ApplicationSettings["driver_status"] = null;
        }
    }
}
