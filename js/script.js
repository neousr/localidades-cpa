const selectProvincia = document.querySelector('select#provincia');
const selectLocalidad = document.querySelector('select#localidad');

// Variables globales
var nombreProvincia, data;

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
        // Obtenemos el índice de la opción seleccionada
        const selectedIndex = selectObj.selectedIndex;
        // Sí el índice es mayor a cero
        if (selectedIndex > 0) {
            // Obtenemos el valor (caracter alfabético) de la opción seleccionada
            const value = selectObj.options[selectedIndex].value;
            // Validamos el caracter alfabético
            if (validCharacter(value)) {
                // Obtenemos el nombre de la Provincia para la muestra
                nombreProvincia = selectObj.options[selectedIndex].textContent;
                // Habilitamos el select de localidades
                selectLocalidad.disabled = false;
                const url = 'server_processing.php';
                const formData = "provincia=" + encodeURIComponent(value);
                // Init Loader
                openLoader();
                // Enviar solicitud al servidor
                sendHttpRequest('POST', url, formData, loadLocalities);
            }
        }
    }
}

// Cargar las localidades
function loadLocalities(response) {
    // Parseamos la respuesta del servidor
    data = JSON.parse(response);
    // Creamos opciones nuevas
    createOptions(data, selectLocalidad);
    closeLoader();
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
        if (xhr.readyState == XMLHttpRequest.DONE) {
            if (xhr.status == 200) {
                if (callback) callback(xhr.responseText);
            }
        }
    }
    xhr.open(method, url + ((/\?/).test(url) ? "&" : "?") + (new Date()).getTime());
    if (data && !(data instanceof FormData)) xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send(data);
}

// Validamos el caracter que forma parte del código 3166-2
function validCharacter(c) {
    // Solo letras mayúsculas son permitidas
    const re = /^[ABCDEFGHJKLMNPQRSTUVWXYZ]{1}$/; // No incluidas => I,Ñ,O
    return re.test(c);
}


const backdrop = document.querySelector('.backdrop');

function closeLoader() {
    if (backdrop) backdrop.style.display = "none";
}

function openLoader() {
    if (backdrop) backdrop.style.display = "block";
}