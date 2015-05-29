using System;
using Microsoft.AspNet.SignalR;
using Microsoft.Owin.Hosting;
using Owin;
using Microsoft.Owin.Cors;
using System.Collections.Generic;

namespace SignalRSelfHost
{
    class Program
    {
        static void Main(string[] args)
        {
            // This will *ONLY* bind to localhost, if you want to bind to all addresses
            // use http://*:8080 to bind to all addresses. 
            // See http://msdn.microsoft.com/en-us/library/system.net.httplistener.aspx 
            // for more information.
            string url = "http://*:8080";
            using (WebApp.Start(url))
            {
                Console.WriteLine("Server is running on {0}", url);
                Console.ReadLine();
            }
        }
    }
    class Startup
    {
        public void Configuration(IAppBuilder app)
        {
            app.UseCors(CorsOptions.AllowAll);
            app.MapSignalR();
        }
    }
    public class MyHub : Hub
    {
        public static Dictionary<string, string> users = new Dictionary<string, string>();

        public void GetUserStatus(string user_id)
        {
            if (users.ContainsKey(user_id))
            {
                Clients.Client(Context.ConnectionId).getUserStatus("Online");
            }
            else
            {
                Clients.Client(Context.ConnectionId).getUserStatus("Offline");
            }
        }

        public void SendPos(string driver_id, string pos)
        {
              Clients.All.getPos(driver_id, pos);
        }

        public void SendPos2(string driver_id, string pos)
        {
            if (users.ContainsKey(driver_id))
            {
                Clients.Client(users[driver_id]).getPos2(driver_id, pos);
            }
        }

        public void SendTracking(string driver_id, string pos)
        {
            if (users.ContainsKey(driver_id))
            {
                Clients.Client(users[driver_id]).getTracking(driver_id, pos);
            }
        }

        public void SetItineraryStatus(string driver_id, string customer_id, string pos)
        {
            if (users.ContainsKey(customer_id))
            {
                Clients.Client(users[customer_id]).getItineraryStatus(driver_id, pos);
            }
        }

        public void SendMessage(string userSendFrom_id, string userSendTo_id, string message)
        {
            if (users.ContainsKey(userSendTo_id))
            {
                Clients.Client(users[userSendTo_id]).getMessage(userSendFrom_id, message);
            }
        }

        public void Connect(string user_id)
        {
            if (!users.ContainsKey(user_id))
            {
                users.Add(user_id, Context.ConnectionId);
            }
            else
            {
                users[user_id] = Context.ConnectionId;
            }
            Clients.All.broadcastMessage(Context.ConnectionId + "#" + user_id, "OK");
        }
    }
}