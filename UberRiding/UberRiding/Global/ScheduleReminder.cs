using Microsoft.Phone.Scheduler;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace UberRiding.Global
{
    class ScheduleReminder
    {
        public static void addScheduleReminder(string name, string title, string content, DateTime beginTime, DateTime expirationTime)
        {
            Reminder reminder = new Reminder(name);
            reminder.Title = title;
            reminder.Content = content;
            reminder.BeginTime = beginTime;
            reminder.ExpirationTime = expirationTime;
            reminder.RecurrenceType = RecurrenceInterval.Daily;
            //reminder.NavigationUri = navigationUri;
            //reminder.

            // Register the reminder with the system.
            ScheduledActionService.Add(reminder);
        }
    }
}
