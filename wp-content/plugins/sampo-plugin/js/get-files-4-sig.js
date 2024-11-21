var files;
var nonce = wpApiSettings.nonce;
var order_id = document.querySelector();
const data = { order_id: order_id };
const result = await fetch("/wp-json/sampo-reg-bis/v1/" + "get_files_to_sig/", {
  method: "GET",
  mode: "cors",
  cache: "no-cache",
  credentials: "same-origin",
  headers: {
    "Content-Type": "application/json",
    "X-WP-Nonce": nonce,
    // 'Content-Type': 'application/x-www-form-urlencoded',
  },
  redirect: "follow", // mdnual, *follow, error
  referrerPolicy: "no-referrer", // no-referrer, *client
  body: JSON.stringify(data),
});
