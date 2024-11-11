function OnApplicationTypeChange() {
  AppType = document.querySelector("#ApplicantType").value;
  if (AppType == 2) {
    document.querySelector("#urlic").hidden = true;
  } else {
    document.querySelector("#urlic").hidden = false;
  }
  console.log("on change!");
}

function OnTaxTypeChange() {
  AppType = document.querySelector("#TaxSystem").value;
  if (AppType == 2) {
    document.querySelector("#TaxObjectGroup").hidden = true;
  } else {
    document.querySelector("#TaxObjectGroup").hidden = false;
  }
  console.log("on tax change!");
}

document
  .querySelector("#sampo-reg-bisnes-form")
  .addEventListener("submit", async function (event) {
    var order_id_element = document.querySelector("#order_id");
    var edit = false;
    if (order_id_element != NULL || order_id_element.value != "") {
      var edit = true;
    }

    var data = {};
    var nonce = wpApiSettings.nonce;

    const form = document.querySelector("#sampo-reg-bisnes-form");
    const formData = new FormData(this);
    formData.forEach(function (value, key) {
      data[key] = value;
    });

    event.preventDefault();
    const result = await fetch(
      "/wp-json/sampo-reg-bis/v1/" + (edit ? "upd_register/" : "register/"),
      {
        method: "POST",
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
      }
    );
    //   window.location.href = "/"
  });
