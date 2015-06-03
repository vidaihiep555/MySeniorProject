#define DEBUG_AGENT
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Navigation;
using Microsoft.Phone.Controls;
using Microsoft.Phone.Shell;
using Microsoft.Phone.Scheduler;

namespace UberRiding
{
    public partial class Page1 : PhoneApplicationPage
    {
        PeriodicTask periodicTask;
        ResourceIntensiveTask resourceIntensiveTask;
        string periodicTaskName = "PeriodicAgent";
        string resourceIntensiveTaskName = "ResourceIntensiveAgent";
        public bool agentsAreEnabled = true;
        public Page1()
        {
            InitializeComponent();
        }


        private void StartPeriodicAgent()
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
                PeriodicStackPanel.DataContext = periodicTask;
                // If debugging is enabled , use LaunchForTest to launch the agent in one minutes
                //#if (DEBUG_AGENT)
                    ScheduledActionService.LaunchForTest(periodicTaskName, TimeSpan.FromSeconds(10));
                //#endif
            }
            catch (InvalidOperationException exception)
            {
                if (exception.Message.Contains("BNS Error: The action is disabled"))
                {
                    MessageBox.Show("Bakcground agents for this application have been disabled by the user.");
                    agentsAreEnabled = false;
                    PeriodicCheckBox.IsChecked = false;
                }
                if (exception.Message.Contains("BNS Error: The maximum number of ScheduledActions of this type have already been added."))
                {
                    // No user action required.
                }
                PeriodicCheckBox.IsChecked = false;
            }
            catch (SchedulerServiceException)
            {
                PeriodicCheckBox.IsChecked =false;
            }
        }

        private void StartResourceIntensiveAgent()
        {
            agentsAreEnabled = true;
            resourceIntensiveTask =  ScheduledActionService.Find(resourceIntensiveTaskName) as ResourceIntensiveTask;
            if (resourceIntensiveTask != null)
            {
                RemoveAgent(resourceIntensiveTaskName);
            }
            resourceIntensiveTask = new ResourceIntensiveTask(resourceIntensiveTaskName);
            try
            {
                ScheduledActionService.Add(resourceIntensiveTask);
                ResourceIntensiveStackPanel.DataContext = resourceIntensiveTask;
                #if (DEBUG_AGENT)
                ScheduledActionService.LaunchForTest(resourceIntensiveTaskName, TimeSpan.FromSeconds(60));
                #endif
            }
            catch (InvalidOperationException exception)
            {
                if (exception.Message.Contains("BNS Error: The action is disabled"))
                {
                    MessageBox.Show("Background agents for this application have been disabled by the user.");
                    agentsAreEnabled = false;
                }
                ResourceIntensiveCheckBox.IsChecked = false;
            }
            catch (SchedulerServiceException)
            {
                ResourceIntensiveCheckBox.IsChecked = false;
            }
        }
        bool ignoreCheckBoxEvents = false;
        private void PeriodicCheckBox_Checked(object sender, RoutedEventArgs e)
        {
            if (ignoreCheckBoxEvents)
            return;
            StartPeriodicAgent();
        }
        private void PeriodicCheckBox_Unchecked(object sender, RoutedEventArgs e)
        {
            if (ignoreCheckBoxEvents)
                    return;
            RemoveAgent(periodicTaskName);
        }
        private void ResourceIntensiveCheckBox_Checked(object sender, RoutedEventArgs e)
        {
            if (ignoreCheckBoxEvents)
            return;
            StartResourceIntensiveAgent();
        }



        private void ResourceIntensiveCheckBox_Unchecked(object sender, RoutedEventArgs e)
        {
            if (ignoreCheckBoxEvents)
                return;
            RemoveAgent(resourceIntensiveTaskName);
        }
        private void RemoveAgent(string name)
        {
            try
            {
                ScheduledActionService.Remove(name);
            }
            catch (Exception)
            {
            }
        }
        protected override void OnNavigatedTo(System.Windows.Navigation.NavigationEventArgs e)
        {
            ignoreCheckBoxEvents = true;
            periodicTask = ScheduledActionService.Find(periodicTaskName) as PeriodicTask;
            if (periodicTask != null)
            {
                PeriodicStackPanel.DataContext = periodicTask;
            }
            resourceIntensiveTask = ScheduledActionService.Find(resourceIntensiveTaskName) as ResourceIntensiveTask;
            if (resourceIntensiveTask != null)
            {
                ResourceIntensiveStackPanel.DataContext = resourceIntensiveTask;
            }
            ignoreCheckBoxEvents = false;
        }
    }
}