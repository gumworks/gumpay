using System;
using System.Threading;
using Xamarin.Forms;
using Xamarin.Forms.Xaml;

namespace GumPayApp2App
{
    public partial class App : Application
    {
        public static string shopKey = "cGwkQgH4TXofJFplxDAFe0PUGWaCZo0IFnIQMncwuBh1VnLQRhwmWHujaxU95sBYGftC7bMoeqjRPVwQEKf6gpOr0RMYuLAsfN4Ax7g9ukjDRgJyRVh8VwCBYCh7QRl2";

        public App()
        {
            InitializeComponent();

            MessagingCenter.Subscribe<Application, string>(this, "AppOpenIntentReceived", (sender, url) =>
            {
                OnAppLinkRequestReceived(new Uri(url));
            });


            MainPage = new MainPage();


        }

        protected override void OnStart()
        {
        }

        protected override void OnSleep()
        {
        }

        protected override void OnResume()
        {
        }

        protected override async void OnAppLinkRequestReceived(Uri uri)
        {
            try
            {
                if (uri.Host == "ordercomplete" && uri.Segments[1] != null)
                {
                    var order = uri.Segments[1];
                    try
                    {
                        var transactionId = await GumPayApp2AppHelper.CheckOrderComplete(App.shopKey, order, new CancellationToken());
                        if (transactionId != Guid.Empty)
                        {
                            MessagingCenter.Send(Application.Current, "logmessage", "Order " + order + " paid successfully with GumPay TransactionId " + transactionId);
                        }
                        else
                        {
                            MessagingCenter.Send(Application.Current, "logmessage", "Order " + order + " not paid");
                        }
                    }
                    catch (Exception ex)
                    { 
                        
                    }
                }
            }
            catch (Exception ex)
            { 
                
            }
        }
    }
}
