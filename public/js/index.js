function request(){
    const date = document.querySelector('#date').value;
    window.location.href = '/?date=' + date;
}

$(document).ready(function() {
    new DataTable('#example');
} );


