let crypto = new CryptoHelper();
let pugin_ready = false;
let global_certsList = new Array();
let global_selectbox_counter = 0;
let global_selectetd_sert = null;
let files_data = new Array();
let signText = "";
let files_list = new Array();

let files_received = false;
var nonce = wpApiSettings.nonce;
var order_id = document.querySelector("#order_id").value;
const data = { order_id: order_id };
fetch("/wp-json/sampo-reg-bis/v1/" + "get_files_to_sig/", {
  method: "POST",
  mode: "cors",
  cache: "no-cache",
  credentials: "same-origin",
  headers: {
    "Content-Type": "application/json",
    "X-WP-Nonce": nonce,
    // 'Content-Type': 'application/x-www-form-urlencoded',
  },
  referrerPolicy: "no-referrer", // no-referrer, *client
  body: JSON.stringify(data),
})
  .then((response) => {
    console.log("get filelist ok!");
    // files_list = JSON.parse(response.body);
    response.json().then((values) => {
      files_list = values;
      console.log(files_list);
      if (files_list != undefined) {
        files_received = true;
        files_list.forEach((file) => {
          fetch(file.url, {
            headers: {
              "X-WP-Nonce": nonce,
            },
          })
            .then((response) => {
              response.blob().then((blob) => {
                const blobUrl = URL.createObjectURL(blob);
                const link = document.createElement("a");
                link.href = blobUrl;
                link.download = file.file_name;
                link.hidden = true;
                console.log(blobUrl);
                document.body.appendChild(link);
                files_data.push({
                  filename: file.file_name,
                  blob: blobUrl,
                  file: blob,
                  name: file.file_elem,
                });
              });
            })
            .catch((e) => {
              console.log(e);
            });
        });
      }
    });
  })
  .catch((e) => {
    console.log(e);
  });

// fetch("/wp-admin/attachmens/sampoEYc7Lh.zip").then(function (response) {
//   if (response.ok) {
//     response.blob().then(function (blob) {
//       objectURL = URL.createObjectURL(blob);
//       signText = blob.text();
//     });
//   } else {
//     console.log(
//       'Network request for "' +
//         product.name +
//         '" image failed with response ' +
//         response.status +
//         ": " +
//         response.statusText
//     );
//   }
// });

crypto
  .init()
  .then(() => {
    pugin_ready = true;
    crypto.getCertificates().then((certs) => {
      globalCertsList = certs;

      list = document.getElementById("sertList");
      certs.forEach((cert) => {
        var oOpt = document.createElement("OPTION");
        oOpt.value = global_selectbox_counter;
        oOpt.text = cert.subject.name;
        list.options.add(oOpt);
        global_certsList.push(cert);
        global_selectbox_counter++;
      });

      // global_selectetd_sert = global_certsList[0];
    });
  })
  .catch(() => {
    // пользователь отклонил запрос
  });

function onChengeSertList(event) {
  global_selectetd_sert = globalCertsList[event.target.value];
}

function doSigFile() {
  // data = signText;
  console.log(files_data);
  files_data.forEach((element, i) => {
    if (global_selectetd_sert == undefined) {
      alert("Выберите сетрификат для подписания!");
      return;
    }
    // console.log(global_selectetd_sert);

    try {
      crypto.sign(global_selectetd_sert, element.file).then((signMessage) => {
        const file = new Blob([signMessage], { type: "text/plain" });
        var formData = new FormData();
        formData.append("action", "sampo_upload_file");
        formData.append("order_id", order_id);
        console.log(element.name);
        formData.append(element.name, file, element.filename + ".sig");
        var emlem = document.querySelector("input[name='fileup_nonce']");
        if (emlem != undefined) {
          formData.append("fileup_nonce", emlem.value);
          console.log(emlem.value);
        }
        emlem = document.querySelector("input[name='_wp_http_referer']");
        if (emlem != undefined) {
          formData.append("_wp_http_referer", emlem.value);
          console.log(emlem.value);
        }
        fetch("/wp-admin/admin-post.php", {
          method: "POST",
          body: formData,
        })
          .then((respone) => {
            console.log(respone);
          })
          .catch((error) => {
            console.log(error);
          });
      });
    } catch (error) {
      console.log(error);
    }
  });
}
