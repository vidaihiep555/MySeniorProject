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
using Newtonsoft.Json.Linq;
using UberRiding.Request;

namespace UberRiding.Driver
{
    public partial class AddVehicle : PhoneApplicationPage
    {
        PhotoChooserTask photoChooserTask;
        CameraCaptureTask cameraCaptureTask;

        public AddVehicle()
        {
            InitializeComponent();
        }

        private void btnSelectVehicalPhoto_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            photoChooserTask = new PhotoChooserTask();
            photoChooserTask.Completed += new EventHandler<PhotoResult>(photoVehicleChooserTask_Completed);
            photoChooserTask.Show();
        }

        private void btnSlelectMotorPlate_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            photoChooserTask = new PhotoChooserTask();
            photoChooserTask.Completed += new EventHandler<PhotoResult>(photoVehiclePlateChooserTask_Completed);
            photoChooserTask.Show();
        }

        private void btnSelectMotorInsuranceImg_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            photoChooserTask = new PhotoChooserTask();
            photoChooserTask.Completed += new EventHandler<PhotoResult>(photoMotorInsuranceChooserTask_Completed);
            photoChooserTask.Show();
        }

        private void btnCaptureVehicalPhoto_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            cameraCaptureTask = new CameraCaptureTask();
            cameraCaptureTask.Completed += new EventHandler<PhotoResult>(cameraCaptureVehicleTask_Completed);
            cameraCaptureTask.Show();
        }

        private void btnCaptureMotoPlate_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            cameraCaptureTask = new CameraCaptureTask();
            cameraCaptureTask.Completed += new EventHandler<PhotoResult>(cameraCaptureVehiclePlateTask_Completed);
            cameraCaptureTask.Show();
        }

        private void btnCaptureMotorInsuranceImg_Tap(object sender, System.Windows.Input.GestureEventArgs e)
        {
            cameraCaptureTask = new CameraCaptureTask();
            cameraCaptureTask.Completed += new EventHandler<PhotoResult>(cameraCaptureMotorInsuranceTask_Completed);
            cameraCaptureTask.Show();
        }

        private void cameraCaptureVehiclePlateTask_Completed(object sender, PhotoResult e)
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

        private void cameraCaptureMotorInsuranceTask_Completed(object sender, PhotoResult e)
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




        private void cameraCaptureVehicleTask_Completed(object sender, PhotoResult e)
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

        private void photoVehicleChooserTask_Completed(object sender, PhotoResult e)
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

        private void photoVehiclePlateChooserTask_Completed(object sender, PhotoResult e)
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

        private void photoMotorInsuranceChooserTask_Completed(object sender, PhotoResult e)
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

        private async void btnAddNewVehicle_Click(object sender, RoutedEventArgs e)
        {
            Dictionary<string, string> postData = new Dictionary<string, string>();
            postData.Add("type", txtbType.Text.Trim());
            postData.Add("license_plate", txtbVehiclePlate.Text.Trim());
            postData.Add("reg_certificate", txtbRegistrationCertificate.Text.Trim());

            postData.Add("vehicle_img", ImageConvert.convertImageToBase64(imgVehicle));
            postData.Add("license_plate_img", ImageConvert.convertImageToBase64(imgLicensePlate));
            postData.Add("motor_insurance_img", ImageConvert.convertImageToBase64(imgMotorInsurance));

            HttpFormUrlEncodedContent content =
                new HttpFormUrlEncodedContent(postData);

            var result = await RequestToServer.sendPostRequest("vehicle", content);

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
                NavigationService.Navigate(new Uri("/Driver/VehicleManagement.xaml", UriKind.RelativeOrAbsolute));
            }



        }
    }
}