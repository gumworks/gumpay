using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Globalization;
using System.Net.Http;
using System.Text;
using System.Threading;
using System.Threading.Tasks;
using Xamarin.Essentials;

namespace GumPayApp2App
{
    public static class GumPayApp2AppHelper
    {
        const string GUMPAY_ENVIRONMENT_URL = "https://api.gumpay.app/";

        /// <summary>
        /// GetOrderLink return a GumPay url that allow user to pay our order. This url can be used in Android/IOS and it will launch GumPay app if it is installed or open a landing page where user can download app and process payment
        /// </summary>
        /// <param name="uniqueKey">Shop unique apikey provided by GumPay Team</param>
        /// <param name="externalOrderId">The order Id in our Shop system, it shoudl be unique to identify this order and need be used later again to retrieve payment status</param>
        /// <param name="amount">The total amount of the order to be paid</param>
        /// <param name="returnUrl">This is the callback url that GumPay app will call once payment completed. It can be our deep app link url like in this example, or some backend url where we will check the payment. We recomend to include in this url the order id in the Shop to make it easy identify and verify the status of the transaction</param>
        /// <param name="cancellationToken"></param>
        /// <returns>Returns string containing the url we need redirect user to</returns>
        public static async Task<string> GetOrderLink(string uniqueKey, string externalOrderId, decimal amount, string returnUrl, CancellationToken cancellationToken)
        {
            var endpointUrl = GUMPAY_ENVIRONMENT_URL + "api/order/getorderlink";
            var response = await ExecuteHttpPostAsync<GenericApiResponse<string>>(endpointUrl, new[]
            {
                new KeyValuePair<string, string>("uniqueKey", uniqueKey),
                new KeyValuePair<string, string>("externalOrderId", externalOrderId),
                new KeyValuePair<string, string>("amount", amount.ToString(new CultureInfo("en-US", false))),
                new KeyValuePair<string, string>("returnUrl", returnUrl),
            }, cancellationToken);
            if (response.Success)
            {
                return response.Data;
            }
            else
            {
                throw new ApiException("Error", response != null ? response.StatusMessage : "Comunication with server failed, please try again");
            }
        }
        /// <summary>
        /// CheckOrderComplete need be used to check if Shop order was already paid or not in GumPay
        /// </summary>
        /// <param name="uniqueKey">Shop unique apikey provided by GumPay Team</param>
        /// <param name="externalOrderId">The order Id in our Shop system. It shoudl be the same order id we sent in the previous GetOrderLink request</param>
        /// <param name="cancellationToken"></param>
        /// <returns>It returns the GumPay transactionId if it was succesfully paid. It returns an empty Guid if the transaction was not paid 00000000-0000-0000-0000-000000000000</returns>
        public static async Task<Guid> CheckOrderComplete(string uniqueKey, string externalOrderId, CancellationToken cancellationToken)
        {
            var endpointUrl = GUMPAY_ENVIRONMENT_URL + "api/order/checkordercomplete";
            var response = await ExecuteHttpPostAsync<GenericApiResponse<Guid>>(endpointUrl, new[]
            {
                new KeyValuePair<string, string>("uniqueKey", uniqueKey),
                new KeyValuePair<string, string>("externalOrderId", externalOrderId),
            }, cancellationToken);
            if (response.Success)
            {
                return response.Data;
            }
            else
            {
                throw new ApiException("Error", response != null ? response.StatusMessage : "Comunication with server failed, please try again");
            }
        }

        private static Dictionary<string, int> ExecuteHttpPostAsyncRetryCount = new Dictionary<string, int>();

        public static async Task<T> ExecuteHttpPostAsync<T>(string url, IEnumerable<KeyValuePair<string, string>> parameters, CancellationToken cancellationToken)
        {
            if (Connectivity.NetworkAccess == NetworkAccess.Internet)
            {
                var _client = new HttpClient();
                var content = new FormUrlEncodedContent(parameters);
                HttpResponseMessage result = null;
                try
                {
                    result = await _client.PostAsync(url, content, cancellationToken);
                    if (ExecuteHttpPostAsyncRetryCount.ContainsKey(url))
                    {
                        ExecuteHttpPostAsyncRetryCount.Remove(url);
                    }
                }
                catch (TaskCanceledException ex)
                {
                    if (!ExecuteHttpPostAsyncRetryCount.ContainsKey(url))
                    {
                        ExecuteHttpPostAsyncRetryCount.Add(url, 0);
                    }
                    if (cancellationToken.IsCancellationRequested == false && ExecuteHttpPostAsyncRetryCount[url]++ < 3)
                    {
                        return await ExecuteHttpPostAsync<T>(url, parameters, cancellationToken);
                    }
                    throw ex;
                }
                catch (Exception ex)
                {
                    throw ex;
                }
                if (result.StatusCode == System.Net.HttpStatusCode.OK)
                {
                    var settings = new Newtonsoft.Json.JsonSerializerSettings();
                    var response = JsonConvert.DeserializeObject<T>(result.Content.ReadAsStringAsync().Result, settings) as IApiResponse;
                    if (response.Success)
                    {
                        return (T)response;
                    }
                    else
                    {
                        throw new ApiException("GumPay APP2APP", response.StatusMessage);
                    }
                }
                else if (result.StatusCode == System.Net.HttpStatusCode.Unauthorized)
                {
                    throw new UnauthorizedAccessException("You are not autorized to do this call");
                }
                else if ((int)result.StatusCode == 555)
                {
                    var settings = new Newtonsoft.Json.JsonSerializerSettings();
                    var response = JsonConvert.DeserializeObject<T>(result.Content.ReadAsStringAsync().Result, settings) as IApiResponse;
                    if (response != null)
                    {
                        throw new ApiException("GumPay APP2APP", response.StatusMessage);
                    }
                    throw new ApiException("GumPay APP2APP", result.Content.ReadAsStringAsync().Result);
                }
                else
                {
                    throw new ApiException("GumPay APP2APP", result.Content.ReadAsStringAsync().Result);
                }
            }
            else
            {
                throw new TimeoutException();
            }
        }



        public class ApiException : Exception
        {
            public ApiException()
            {
                this.Title = "GumPay APP2APP";
            }
            public ApiException(IApiResponse response) : base(response.StatusMessage)
            {
                this.Title = "GumPay APP2APP";
            }
            public ApiException(string title, string message) : base(message)
            {
                this.Title = title;
            }
            public ApiException(string message) : base(message)
            {
                this.Title = "GumPay APP2APP";
            }
            public string Title { get; set; }
        }
        public interface IApiResponse
        {
            string StatusMessage { get; set; }
            bool Success { get; set; }
            string Token { get; set; }
        }
        [JsonObject(ItemNullValueHandling = NullValueHandling.Ignore)]
        public class ApiResponse : IApiResponse
        {
            public ApiResponse()
            {
                this.Success = true;
            }
            public ApiResponse(ApiException ex)
            {
                this.Success = false;
                this.StatusMessage = ex.Message;
            }
            public ApiResponse(string excepionMessage)
            {
                this.Success = false;
                this.StatusMessage = excepionMessage;
            }
            public bool Success { get; set; }
            public string StatusMessage { get; set; }
            public string Token { get; set; }
        }
        public class GenericApiResponse<T> : ApiResponse
        {
            public GenericApiResponse()
            {
            }
            public GenericApiResponse(T data)
            {
                this.Data = data;
            }
            public GenericApiResponse(T data, string token)
            {
                this.Data = data;
                this.Token = token;
            }
            public T Data { get; set; }
        }
    }
}
