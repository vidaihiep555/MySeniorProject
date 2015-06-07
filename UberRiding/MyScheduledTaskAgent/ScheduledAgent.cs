using System.Diagnostics;
using System.Windows;
using Microsoft.Phone.Scheduler;
using Microsoft.Phone.Shell;
using Microsoft.AspNet.SignalR.Client;
using System.Windows.Threading;
using System.Net.Http;
using System;

namespace MyScheduledTaskAgent
{
    public class ScheduledAgent : ScheduledTaskAgent
    {
        private IHubProxy HubProxy { get; set; }
        const string ServerURI = "http://52.25.218.73:8080/signalr";

        private HubConnection con { get; set; }
        /// <remarks>
        /// ScheduledAgent constructor, initializes the UnhandledException handler
        /// </remarks>
        static ScheduledAgent()
        {
            // Subscribe to the managed exception handler
            Deployment.Current.Dispatcher.BeginInvoke(delegate
            {
                Application.Current.UnhandledException += UnhandledException;
            });
        }

        /// Code to execute on Unhandled Exceptions
        private static void UnhandledException(object sender, ApplicationUnhandledExceptionEventArgs e)
        {
            if (Debugger.IsAttached)
            {
                // An unhandled exception has occurred; break into the debugger
                Debugger.Break();
            }
        }

        /// <summary>
        /// Agent that runs a scheduled task
        /// </summary>
        /// <param name="task">
        /// The invoked task
        /// </param>
        /// <remarks>
        /// This method is called when a periodic or resource intensive task is invoked
        /// </remarks>
        protected override void OnInvoke(ScheduledTask task)
        {
            //TODO: Add code to perform your task in background
            string x = task.Name;
            string id = x.Substring(12, x.Length - 13);
            ConnectAsync(id);

            
        }


        private async void ConnectAsync(string id)
        {
            con = new HubConnection(ServerURI);
            con.Closed += Connection_Closed;
            con.Error += Connection_Error;
            HubProxy = con.CreateHubProxy("MyHub");
            //Handle incoming event from server: use Invoke to write to console from SignalR's thread
            HubProxy.On<string, string>("getPos2", (driver_id, message) =>
                //Dispatcher.CurrentDispatcher.BeginInvoke(() => test(message))

                Deployment.Current.Dispatcher.BeginInvoke(() => test(message))

            );
            try
            {
                await con.Start();
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

                //string id = "C" + Global.GlobalData.user_id;
                //string id = "D1";
                HubProxy.Invoke("Connect", id);
            });
        }

        private void test(string message)
        {
            string[] latlng = message.Split(",".ToCharArray());
            //double lat = Double.Parse(latlng[0]);
            //double lng = Double.Parse(latlng[1]);
            //addMarkertoMap(new GeoCoordinate(lat, lng));
            //txtFireBase.Text = message;
            //ToastPrompt c = new ToastPrompt();
            //c.Title = "dsadasd";
            //c.Message = "zzzzzzzzzzzzzzzzzz";
            //c.Show();
            string ToastMessage = string.Empty;

            //if (task is PeriodicTask)
            //{
            ToastMessage = "Vidaihiep";
            //}
            ShellToast Toast = new ShellToast();
            Toast.Title = "Demama";
            Toast.Content = ToastMessage;
            Toast.Show();
            NotifyComplete();
        }

        private void Connection_Error(Exception obj)
        {
            //txtFireBase.Text = "error";
        }

        /// <summary>
        /// If the server is stopped, the connection will time out after 30 seconds (default), and the 
        /// Closed event will fire.
        /// </summary>
        private void Connection_Closed()
        {
            //Deactivate chat UI; show login UI. 
        }
    }
}