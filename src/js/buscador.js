document.addEventListener("DOMContentLoaded", function () {
    iniciarApp();
})

function iniciarApp() {
    buscarPorFecha();
}

function buscarPorFecha() {
    const fechaInput = document.querySelector('#fecha');
    fechaInput.addEventListener('input', (e) => {
        const fecha = e.target.value;
        // buscarPorFecha(fecha);
        // console.log(e.target.value);
        window.location = `?fecha=${fecha}`
    })
}