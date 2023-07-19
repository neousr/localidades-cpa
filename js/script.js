'use strict';

const d = document;
const selectProvincia = d.querySelector('select#provincia');
const selectLocalidad = d.querySelector('select#localidad');

// Variables globales
let nombreProvincia, data;
const URL = 'server_processing.php';

d.addEventListener('DOMContentLoaded', () => {
    initChangeProvincia();
    initChangeLocalidad();
});

// Inicializar el cambio de Provincia
function initChangeProvincia() {
    if (selectProvincia) {
        selectProvincia.onchange = function (e) { return handleChangeProvincia(this, e); }
    }
}

// Manejar el cambio de Provincia
function handleChangeProvincia(selectEl, objEvent) {
    if (selectLocalidad) {
        // Solo se habilitará cuando el índice seleccionado sea distinto de cero
        selectLocalidad.disabled = true;
        // Removemos las opciones actuales del select de localidades
        removeOptions(selectLocalidad);
        // Removemos los datos de muestra, si los hay
        output('');
        // Obtenemos del select de provincias el índice de la opción seleccionada
        const selectedIndex = selectEl.selectedIndex;
        // Sí el índice es mayor a cero y menor a 25
        if (0 < selectedIndex && selectedIndex < 25) {
            // Obtenemos el valor (caracter alfabético) de la opción seleccionada
            const char = selectEl.options[selectedIndex].value;
            // Validamos el caracter que forma parte del código 3166-2, solo un carácter, no incluidos => I,Ñ,O
            if (/^[A-HJ-NP-Z]$/.test(char)) {
                if (char === 'C') {
                    nombreProvincia = "CIUDAD AUTONOMA DE BUENOS AIRES";
                    data = [{ id: "5001", nombre: "CIUDAD AUTONOMA DE BUENOS AIRES", partido: "", cp: "" }];
                    createOptions(data, selectLocalidad);
                } else {
                    // Obtenemos el nombre de la Provincia para la muestra
                    nombreProvincia = selectEl.options[selectedIndex].innerText;
                    // Enviar solicitud al servidor
                    sendHttpRequest('POST', URL, "provincia=" + encodeURIComponent(char), response => {
                        loadLocalities(response);
                    });
                }
                // Habilitamos el select de localidades
                selectLocalidad.disabled = false;
            } else {
                console.log('Lo sentimos, no podemos procesar su solicitud!');
            }
        }
    }
}

// Cargar las localidades
function loadLocalities(response) {
    if (response !== 'Error') {
        // Parseamos la respuesta del servidor en la variable data para uso posterior
        data = JSON.parse(response);
        // Creamos opciones con los datos recuperados
        createOptions(data, selectLocalidad);
    } else {
        console.log('Algo salió mal!');
    }
}

// Inicializar el cambio de Localidad
function initChangeLocalidad() {
    if (selectLocalidad) {
        // En la carga del DOM desabilitamos el select de localidad
        selectLocalidad.disabled = true;
        selectLocalidad.onchange = function (e) { return handleChangeLocalidad(this, e); }
    }
}

// Manejar el cambio de Localidad
function handleChangeLocalidad(selectEl, objEvent) {
    const selectedIndex = selectEl.selectedIndex;
    let message = '';
    if (selectedIndex > 0) {
        const obj = data[selectedIndex - 1];
        message += `<div class="output"><h2>${obj.nombre}, ${nombreProvincia}</h2><h3>Código postal: ${obj.cp}</h3></div>`;
    }
    // Mostrar datos
    output(message);
}

// Crea opciones en objetos select
function createOptions(data, selectEl) {
    let newOpt;
    const fragment = d.createDocumentFragment();
    data.forEach(el => {
        newOpt = d.createElement('option');
        newOpt.value = el.id;
        newOpt.text = el.nombre + " (" + el.partido + ")";
        try {
            fragment.add(newOpt);
        } catch (error) {
            fragment.appendChild(newOpt);
        }
    });
    selectEl.appendChild(fragment);
}

// Remueve todas las opciones excepto el índice 0 (el marcador de posición)
function removeOptions(selectEl) {
    let len = selectEl.options.length;
    while (len-- > 1) selectEl.remove(1);
}

// Datos de salida
function output(message) {
    const output = d.querySelector('#output');
    if (output) output.innerHTML = message;
}

// Enviar solicitud al servidor
function sendHttpRequest(method, url, data, callback) {
    const xhr = getXhr();
    xhr.onreadystatechange = processRequest;
    function getXhr() {
        if (window.XMLHttpRequest) {
            return new XMLHttpRequest();
        } else {
            return new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
    function processRequest() {
        if (xhr.readyState == xhr.DONE) {
            if (xhr.status == 200 && xhr.response != null) {
                callback(xhr.response);
                // console.log(xhr.getResponseHeader("Content-Type"));
            } else {
                console.log("There was a problem retrieving the data: " + xhr.statusText);
            }
        }
    }

    const overlay = d.createElement("div");
    overlay.id = "overlay";
    const loader = d.createElement("div");
    loader.setAttribute("id", "loader");

    overlay.appendChild(loader);

    xhr.open(method, url + ((/\?/).test(url) ? "&" : "?") + (new Date()).getTime());
    xhr.onloadstart = function (e) {
        d.body.appendChild(overlay);
    }
    xhr.onloadend = function (e) {
        overlay.remove();
    }
    if (data && !(data instanceof FormData)) xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send(data);
    xhr.onerror = function (e) {
        console.log("Error: " + e + " Could not load url.");
    }
}
