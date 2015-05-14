using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Navigation;
using Microsoft.Phone.Controls;
using Microsoft.Phone.Shell;
using Microsoft.Phone.Tasks;
using UberRiding.Global;
using Windows.Web.Http;
using UberRiding.Request;
using Newtonsoft.Json.Linq;

namespace UberRiding.Driver
{
    public partial class VehicleDetails : PhoneApplicationPage
    {
        PhotoChooserTask photoChooserTask;
        CameraCaptureTask cameraCaptureTask;

        public VehicleDetails()
        {
            InitializeComponent();
        }

        protected override void OnNavigatedTo(NavigationEventArgs e)
        {
            base.OnNavigatedTo(e);

            getVehicleInfo();
        }

        public void getVehicleInfo()
        {

            //get info
            txtbType.Text = Global.GlobalData.selectedVehicle.type;
            txtbVehiclePlate.Text = Global.GlobalData.selectedVehicle.license_plate;
            txtbRegistrationCertificate.Text = Global.GlobalData.selectedVehicle.reg_certificate;
            //get photo
            imgVehicle.Source = Global.GlobalData.selectedVehicle.vehicle_img;
            imgLicensePlate.Source = Global.GlobalData.selectedVehicle.license_plate_img;
            imgMotorInsurance.Source = Global.GlobalData.selectedVehicle.motor_insurance_img;

        }

        private async void updateVehicleInfo(string type, string motorplate, string reg_certificate)
        {
            Dictionary<string, string> postData = new Dictionary<string, string>();
            postData.Add("type", type);
            postData.Add("license_plate", motorplate);
            postData.Add("reg_certificate", reg_certificate);

            postData.Add("vehicle_img", ImageConvert.convertImageToBase64(imgVehicle));
            postData.Add("license_plate_img", ImageConvert.convertImageToBase64(imgLicensePlate));
            postData.Add("motor_insurance_img", ImageConvert.convertImageToBase64(imgMotorInsurance));

            HttpFormUrlEncodedContent content =
                new HttpFormUrlEncodedContent(postData);

            var result = await RequestToServer.sendPutRequest("vehicle/" + Global.GlobalData.selectedVehicle.vehicle_id, content);

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
                NavigationService.Navigate(new Uri("/Refresh.xaml", UriKind.RelativeOrAbsolute));
            }

        }

        #region Image
        void photoVehicleChooserTask_Completed(object sender, PhotoResult e)
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

                imgVehicle.Source = ImageConvert.convertBase64ToImage(str);
                //MessageBox.Show(str);
            }
        }

        void photoLicensePlateChooserTask_Completed(object sender, PhotoResult e)
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

                imgLicensePlate.Source = ImageConvert.convertBase64ToImage(str);
                //MessageBox.Show(str);
            }
        }

        void photoMotorInsuranceChooserTask_Completed(object sender, PhotoResult e)
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

                imgMotorInsurance.Source = ImageConvert.convertBase64ToImage(str);
                //MessageBox.Show(str);
            }
        }

        void cameraCaptureVehicleTask_Completed(object sender, PhotoResult e)
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

                imgVehicle.Source = ImageConvert.convertBase64ToImage(str);
            }
        }

        void cameraCaptureLicensePlateTask_Completed(object sender, PhotoResult e)
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

                imgLicensePlate.Source = ImageConvert.convertBase64ToImage(str);
            }
        }

        void cameraCaptureMotorInsuranceTask_Completed(object sender, PhotoResult e)
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

                imgMotorInsurance.Source = ImageConvert.convertBase64ToImage(str);
            }
        }

        private void btnSelectVehiclePhoto_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            photoChooserTask = new PhotoChooserTask();
            photoChooserTask.Completed += new EventHandler<PhotoResult>(photoVehicleChooserTask_Completed);
            photoChooserTask.Show();
        }

        private void btnSlelectMotorPlate_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            photoChooserTask = new PhotoChooserTask();
            photoChooserTask.Completed += new EventHandler<PhotoResult>(photoLicensePlateChooserTask_Completed);
            photoChooserTask.Show();
        }

        private void btnSelectMotorInsuranceImg_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            photoChooserTask = new PhotoChooserTask();
            photoChooserTask.Completed += new EventHandler<PhotoResult>(photoMotorInsuranceChooserTask_Completed);
            photoChooserTask.Show();
        }

        private void btnCaptureMotoPlate_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            cameraCaptureTask = new CameraCaptureTask();
            cameraCaptureTask.Completed += new EventHandler<PhotoResult>(cameraCaptureLicensePlateTask_Completed);
            cameraCaptureTask.Show();
        }

        private void btnCaptureMotorInsuranceImg_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            cameraCaptureTask = new CameraCaptureTask();
            cameraCaptureTask.Completed += new EventHandler<PhotoResult>(cameraCaptureMotorInsuranceTask_Completed);
            cameraCaptureTask.Show();
        }

        private void btnCaptureVehiclePhoto_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            cameraCaptureTask = new CameraCaptureTask();
            cameraCaptureTask.Completed += new EventHandler<PhotoResult>(cameraCaptureVehicleTask_Completed);
            cameraCaptureTask.Show();
        }
        #endregion

        private void btnUpdate_Click(object sender, RoutedEventArgs e)
        {
            StackPanel panel = new StackPanel();

            TextBox txtbVehicleType = new TextBox();
            TextBox txtbVehiclePlateNum = new TextBox();
            TextBox txtbVehicleReg = new TextBox();

            txtbVehiclePlateNum.Text = txtbVehiclePlate.Text;
            txtbVehicleType.Text = txtbType.Text;
            txtbVehicleReg.Text = txtbRegistrationCertificate.Text;

            TextBlock b0 = new TextBlock(); b0.Text = "Vehicle type: ";
            TextBlock b1 = new TextBlock(); b1.Text = "Vehicle plate's number: ";
            TextBlock b2 = new TextBlock(); b2.Text = "Register certificate: ";


            panel.Children.Add(b0);
            panel.Children.Add(txtbVehicleType);
            panel.Children.Add(b1);
            panel.Children.Add(txtbVehiclePlateNum);
            panel.Children.Add(b2);
            panel.Children.Add(txtbVehicleReg);

            CustomMessageBox messageBox = new CustomMessageBox()
            {
                //set the properties
                Caption = "Update vehicle information",
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

                        updateVehicleInfo(txtbVehicleType.Text.Trim(), txtbVehiclePlateNum.Text.Trim(), txtbVehicleReg.Text.Trim());

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

            NavigationService.Navigate(new Uri("/Driver/VehicleManagement.xaml", UriKind.RelativeOrAbsolute));
        }
    }
}