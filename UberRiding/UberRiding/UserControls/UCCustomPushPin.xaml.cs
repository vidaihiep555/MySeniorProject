﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Navigation;
using Microsoft.Phone.Controls;
using Microsoft.Phone.Shell;

namespace UberRiding.UserControls
{
    public partial class UCCustomPushPin : UserControl
    {
        private string _description;

        public string Description
        {
            get { return _description; }
            set { _description = value; }
        }

        public UCCustomPushPin()
        {
            InitializeComponent();
        }
      
    }
}
