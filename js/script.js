const selectProvincia = document.querySelector('select#provincia');
const selectLocalidad = document.querySelector('select#localidad');
const backdrop = document.querySelector('.backdrop');

// Variables globales
var nombreProvincia, data;
const url = 'server_processing.php';

document.addEventListener('DOMContentLoaded', () => {
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
function handleChangeProvincia(selectObj, objEvent) {
    if (selectLocalidad) {
        // Solo se habilitará cuando el índice seleccionado sea distinto de cero
        selectLocalidad.disabled = true;
        // Removemos las opciones actuales del select de localidades
        removeOptions(selectLocalidad);
        // Removemos los datos de muestra, si los hay
        output('');
        // Obtenemos del select de provincias el índice de la opción seleccionada
        const selectedIndex = selectObj.selectedIndex;
        // Sí el índice es mayor a cero
        if (selectedIndex > 0) {
            // Obtenemos el valor (caracter alfabético) de la opción seleccionada
            const char = selectObj.options[selectedIndex].value;
            // Validamos el caracter alfabético
            if (validCharacter(char)) {
                // Obtenemos el nombre de la Provincia para la muestra
                nombreProvincia = selectObj.options[selectedIndex].textContent;
                // Habilitamos el select de localidades
                selectLocalidad.disabled = false;
                // Enviar solicitud al servidor
                sendHttpRequest('POST', url, "provincia=" + encodeURIComponent(char), loadLocalities);
            } else {
                alert('Lo sentimos, no podemos procesar su solicitud!');
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
function handleChangeLocalidad(selectObj, objEvent) {
    const selectedIndex = selectObj.selectedIndex;
    let message = '';
    if (selectedIndex > 0) {
        const obj = data[selectedIndex - 1];
        message += `<div class="output"><h2>${obj.nombre}, ${nombreProvincia}</h2><h3>Código postal: ${obj.cp}</h3></div>`;
    }
    // Mostrar datos
    output(message);
}

// Crea opciones en objetos select
function createOptions(data, selectObj) {
    let newOpt;
    const fragment = document.createDocumentFragment();
    data.forEach(obj => {
        newOpt = document.createElement('option');
        newOpt.value = obj.id;
        newOpt.text = obj.nombre + " (" + obj.cp + ")";
        try {
            fragment.add(newOpt);
        } catch (error) {
            fragment.appendChild(newOpt);
        }
    });
    selectObj.appendChild(fragment);
}

// Remueve todas las opciones excepto el índice 0 (el marcador de posición)
function removeOptions(selectObj) {
    let len = selectObj.options.length;
    while (len-- > 1) selectObj.remove(1);
}

// Datos de salida
function output(message) {
    const output = document.querySelector('#output');
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
            } else {
                console.log("There was a problem retrieving the data: " + xhr.statusText);
            }
        }
    }
    xhr.open(method, url + ((/\?/).test(url) ? "&" : "?") + (new Date()).getTime());
    xhr.onloadstart = function (e) {
        openLoader();
    }
    xhr.onloadend = function (e) {
        // console.log(e.loaded);
        closeLoader();
    }
    if (data && !(data instanceof FormData)) xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send(data);
    xhr.onerror = function (e) {
        console.log("Error: " + e + " Could not load url.");
    }
}

// Validamos el caracter que forma parte del código 3166-2
function validCharacter(c) {
    // Solo letras mayúsculas son permitidas
    return /^[ABCDEFGHJKLMNPQRSTUVWXYZ]{1}$/.test(c);// No incluidas => I,Ñ,O
}

function closeLoader() {
    if (backdrop) backdrop.style.cssText = "display: none;";
}

function openLoader() {
    if (backdrop) backdrop.style.display = "block";
}
