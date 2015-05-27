 using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Navigation;
using Microsoft.Phone.Controls;
using Microsoft.Phone.Shell;
using Windows.Web.Http;
using UberRiding.Global;
using Newtonsoft.Json.Linq;
using UberRiding.Request;
using Microsoft.Phone.Tasks;

namespace UberRiding.Driver
{
    public partial class DriverAccInfo : PhoneApplicationPage
    {
        PhotoChooserTask photoChooserTask;
        CameraCaptureTask cameraCaptureTask;

        public DriverAccInfo()
        {
            InitializeComponent();

            if (GlobalData.isDriver)
            {
                getUserInfo();

                //them button
            }
            else
            {
                customerGetUserInfo();
            }
            
        }

        public async void customerGetUserInfo()
        {
            var result = await RequestToServer.sendGetRequest("customergetdriver/"+GlobalData.calldriver);

            JObject jsonObject = JObject.Parse(result);

            txtbEmail.Text = jsonObject.Value<string>("email");
            txtbFullname.Text = jsonObject.Value<string>("fullname");
            txtbPhone.Text = jsonObject.Value<string>("phone");
            txtbPersonalID.Text = jsonObject.Value<string>("personalID");

            //set Image
            imgAvatar.Source = ImageConvert.convertBase64ToImage(jsonObject.Value<string>("driver_avatar").Trim());
            imgPersonalID.Source = ImageConvert.convertBase64ToImage(jsonObject.Value<string>("personalID_img").Trim());
        }

        public async void getUserInfo()
        {
            var result = await RequestToServer.sendGetRequest("driver");

            JObject jsonObject = JObject.Parse(result);

            txtbEmail.Text = jsonObject.Value<string>("email");
            txtbFullname.Text = jsonObject.Value<string>("fullname");
            txtbPhone.Text = jsonObject.Value<string>("phone");
            txtbPersonalID.Text = jsonObject.Value<string>("personalID");

            //set Image
            imgAvatar.Source = ImageConvert.convertBase64ToImage(jsonObject.Value<string>("driver_avatar").Trim());
            imgPersonalID.Source = ImageConvert.convertBase64ToImage(jsonObject.Value<string>("personalID_img").Trim());
        }

        private void btnChangePassword_Click(object sender, RoutedEventArgs e)
        {
            StackPanel panel = new StackPanel();

            TextBox txtbNewPassword = new TextBox();
            TextBox txtbConfirmNewPassword = new TextBox();

            TextBlock b1 = new TextBlock(); b1.Text = "Password: ";
            TextBlock b2 = new TextBlock(); b2.Text = "Confirm Password: ";
            panel.Children.Add(b1);
            panel.Children.Add(txtbNewPassword);
            panel.Children.Add(b2);
            panel.Children.Add(txtbConfirmNewPassword);
            CustomMessageBox messageBox = new CustomMessageBox()
            {
                //set the properties
                Caption = "Update your password",
                Message = "",
                LeftButtonContent = "Change",
                RightButtonContent = "No"
            };

            messageBox.Content = panel;
            //messageBox.Content = b2;

            //Add the dismissed event handler
            messageBox.Dismissed += (s1, e1) =>
            {
                switch (e1.Result)
                {
                    case CustomMessageBoxResult.LeftButton:
                        //add the task you wish to perform when user clicks on yes button here
                        if (txtbNewPassword.Text.Trim().Equals(txtbConfirmNewPassword.Text.Trim()))
                        {
                            updatePassword(txtbNewPassword.Text.Trim());
                        }
                        else
                        {
                            MessageBox.Show("Password not match");
                        }

                        break;
                    case CustomMessageBoxResult.RightButton:
                        //add the task you wish to perform when user clicks on no button here

                        break;
                    case CustomMessageBoxResult.None:
                        // Do something.
                        break;
                    default:
                        break;
                }
            };

            //add the show method
            messageBox.Show();
        }

        public async void updateUserInfo(string fullname, string email, string personalID, string phone)
        {
            Dictionary<string, string> postData = new Dictionary<string, string>();
            postData.Add("fullname", fullname);
            postData.Add("email", email);
            postData.Add("personalID", personalID);
            postData.Add("phone", phone);

            postData.Add("driver_avatar", ImageConvert.convertImageToBase64(imgAvatar));
            postData.Add("personalID_img", ImageConvert.convertImageToBase64(imgAvatar));

            HttpFormUrlEncodedContent content =
                new HttpFormUrlEncodedContent(postData);
            var result = await RequestToServer.sendPutRequest("driver", content);

            JObject jsonObject = JObject.Parse(result);
            if (jsonObject.Value<bool>("error"))
            {
                MessageBox.Show(jsonObject.Value<string>("message"));
            }
            else
            {
                //Global.GlobalData.isDriver = true;
                MessageBox.Show(jsonObject.Value<string>("message"));
                // refresh lai trang
                NavigationService.Navigate(new Uri("/RefreshPage.xaml", UriKind.RelativeOrAbsolute));
            }
        }

        public async void updatePassword(string newPassword)
        {
            Dictionary<string, string> postData = new Dictionary<string, string>();
            postData.Add("password", newPassword);

            HttpFormUrlEncodedContent content =
                new HttpFormUrlEncodedContent(postData);
            var result = await RequestToServer.sendPutRequest("driver", content);

            JObject jsonObject = JObject.Parse(result);
            MessageBox.Show(jsonObject.Value<string>("message"));
        }

        #region Image
        void photoAvatarChooserTask_Completed(object sender, PhotoResult e)
        {
            if (e.TaskResult == TaskResult.OK)
            {
                //Code to display the photo on the page in an image control named myImage.
                System.Windows.Media.Imaging.BitmapImage bmp = new System.Windows.Media.Imaging.BitmapImage();
                bmp.SetSource(e.ChosenPhoto);

                //MessageBox.Show(bmp.ToString());
                Image myImgage = new Image();
                myImgage.Source = bmp;
                string str = ImageConvert.convertImageToBase64(myImgage);

                imgAvatar.Source = ImageConvert.convertBase64ToImage(str);
                //MessageBox.Show(str);
            }
        }

        void photoPerIDChooserTask_Completed(object sender, PhotoResult e)
        {
            if (e.TaskResult == TaskResult.OK)
            {
                //Code to display the photo on the page in an image control named myImage.
                System.Windows.Media.Imaging.BitmapImage bmp = new System.Windows.Media.Imaging.BitmapImage();
                bmp.SetSource(e.ChosenPhoto);

                //MessageBox.Show(bmp.ToString());
                Image myImgage = new Image();
                myImgage.Source = bmp;
                string str = ImageConvert.convertImageToBase64(myImgage);

                imgPersonalID.Source = ImageConvert.convertBase64ToImage(str);
                //MessageBox.Show(str);
            }
        }

        void cameraCaptureAvaTask_Completed(object sender, PhotoResult e)
        {
            if (e.TaskResult == TaskResult.OK)
            {
                //Code to display the photo on the page in an image control named myImage.
                System.Windows.Media.Imaging.BitmapImage bmp = new System.Windows.Media.Imaging.BitmapImage();
                bmp.SetSource(e.ChosenPhoto);

                //MessageBox.Show(bmp.ToString());
                Image myImgage = new Image();
                myImgage.Source = bmp;
                string str = ImageConvert.convertImageToBase64(myImgage);

                imgAvatar.Source = ImageConvert.convertBase64ToImage(str);
            }
        }

        void cameraCapturePerIDTask_Completed(object sender, PhotoResult e)
        {
            if (e.TaskResult == TaskResult.OK)
            {
                //Code to display the photo on the page in an image control named myImage.
                System.Windows.Media.Imaging.BitmapImage bmp = new System.Windows.Media.Imaging.BitmapImage();
                bmp.SetSource(e.ChosenPhoto);

                //MessageBox.Show(bmp.ToString());
                Image myImgage = new Image();
                myImgage.Source = bmp;
                string str = ImageConvert.convertImageToBase64(myImgage);

                imgPersonalID.Source = ImageConvert.convertBase64ToImage(str);
            }
        }

        private void btnSelectPerIDImg_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            photoChooserTask = new PhotoChooserTask();
            photoChooserTask.Completed += new EventHandler<PhotoResult>(photoPerIDChooserTask_Completed);
            photoChooserTask.Show();
        }

        private void btnCapturePerIDImg_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            cameraCaptureTask = new CameraCaptureTask();
            cameraCaptureTask.Completed += new EventHandler<PhotoResult>(cameraCapturePerIDTask_Completed);
            cameraCaptureTask.Show();
        }

        private void btnSelectAvaImg_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            photoChooserTask = new PhotoChooserTask();
            photoChooserTask.Completed += new EventHandler<PhotoResult>(photoAvatarChooserTask_Completed);
            photoChooserTask.Show();
        }

        private void btnCaptureAvaImg_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            cameraCaptureTask = new CameraCaptureTask();
            cameraCaptureTask.Completed += new EventHandler<PhotoResult>(cameraCaptureAvaTask_Completed);
            cameraCaptureTask.Show();
        }
        #endregion

        private void btnUpdateProfile_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            StackPanel panel = new StackPanel();

            TextBox txtbUpdateFullname = new TextBox();
            TextBox txtbUpdateEmail = new TextBox();
            TextBox txtbUpdatePhone = new TextBox();
            TextBox txtbUpdatePersonalID = new TextBox();

            txtbUpdateEmail.Text = txtbEmail.Text;
            txtbUpdatePersonalID.Text = txtbPersonalID.Text;
            txtbUpdatePhone.Text = txtbPhone.Text;
            txtbUpdateFullname.Text = txtbFullname.Text;
            TextBlock b0 = new TextBlock(); b0.Text = "Full Name: ";
            TextBlock b1 = new TextBlock(); b1.Text = "Email: ";
            TextBlock b2 = new TextBlock(); b2.Text = "Personal ID: ";
            TextBlock b3 = new TextBlock(); b3.Text = "Phone: ";

            panel.Children.Add(b0);
            panel.Children.Add(txtbUpdateFullname);
            panel.Children.Add(b1);
            panel.Children.Add(txtbUpdateEmail);
            panel.Children.Add(b2);
            panel.Children.Add(txtbUpdatePersonalID);
            panel.Children.Add(b3);
            panel.Children.Add(txtbUpdatePhone);
            CustomMessageBox messageBox = new CustomMessageBox()
            {
                //set the properties
                Caption = "Update your profile",
                Message = "",
                LeftButtonContent = "Update",
                RightButtonContent = "Cancel"
            };

            messageBox.Content = panel;
            //messageBox.Content = b2;

            //Add the dismissed event handler
            messageBox.Dismissed += (s1, e1) =>
            {
                switch (e1.Result)
                {
                    case CustomMessageBoxResult.LeftButton:
                        //add the task you wish to perform when user clicks on yes button here

                        updateUserInfo(txtbUpdateFullname.Text.Trim(), txtbUpdateEmail.Text.Trim(),
                            txtbUpdatePersonalID.Text.Trim(), txtbUpdatePhone.Text.Trim());

                        break;
                    case CustomMessageBoxResult.RightButton:
                        //add the task you wish to perform when user clicks on no button here

                        break;
                    case CustomMessageBoxResult.None:
                        // Do something.
                        break;
                    default:
                        break;
                }
            };

            //add the show method
            messageBox.Show();
        }

        private void btnVehicle_Click(object sender, RoutedEventArgs e)
        {
            NavigationService.Navigate(new Uri("/Driver/VehicleManagement.xaml", UriKind.RelativeOrAbsolute));
        }
    }
}