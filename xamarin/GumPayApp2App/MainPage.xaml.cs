using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Xamarin.Essentials;
using Xamarin.Forms;

namespace GumPayApp2App
{
    public partial class MainPage : ContentPage
    {
        public MainPage()
        {
            InitializeComponent();
            MessagingCenter.Subscribe<Application, string>(this, "logmessage", async (sender, message) => {
                labelLog.Text += message + Environment.NewLine;
            });
            MessagingCenter.Send(Application.Current, "logmessage", "GumPay App2App payment demo ready");
        }
        private async void Button_Clicked(object sender, EventArgs e)
        {
            var order = Guid.NewGuid().ToString().Replace("-", "");
            MessagingCenter.Send(this, "logmessage", "Requesting GumPay link for order " + order);

            var urlLink = await GumPayApp2AppHelper.GetOrderLink(App.shopKey, order, 1, "gumpay2app://ordercomplete/" + order, new System.Threading.CancellationToken());
            MessagingCenter.Send(Application.Current, "logmessage", "Opening external url " + urlLink);
            await Launcher.OpenAsync(urlLink);
        }


    }
}
