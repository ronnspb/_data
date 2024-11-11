let crypto = new CryptoHelper();
let pugin_ready = false;
let global_certsList = new Array();
let global_selectbox_counter = 0;
let global_selectetd_sert = null;
let files = new Array();
let signText = "";
fetch("/wp-admin/attachmens/sampoEYc7Lh.zip").then(function (response) {
  if (response.ok) {
    response.blob().then(function (blob) {
      objectURL = URL.createObjectURL(blob);
      signText = blob.text();
    });
  } else {
    console.log(
      'Network request for "' +
        product.name +
        '" image failed with response ' +
        response.status +
        ": " +
        response.statusText
    );
  }
});

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
  data = signText;
  if (global_selectetd_sert == undefined) {
    alert("Выберите сетрификат для подписания!");
    return;
  }
  // console.log(global_selectetd_sert);

  try {
    crypto.sign(global_selectetd_sert, data).then((signMessage) => {
      console.log(signMessage);
    });
  } catch (error) {
    console.log(error);
  }
}
