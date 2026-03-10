// Attendance Frontend Scripts
setInterval(displayclock, 500);
function displayclock() {
    var time = new Date();
    var hrs = time.getHours();
    var min = time.getMinutes();
    var sec = time.getSeconds();
    var en = 'AM';
    if (hrs > 12) { en = 'PM'; }
    if (hrs > 12) { hrs = hrs - 12; }
    if (hrs == 0) { hrs = 12; }
    if (hrs < 10) { hrs = '0' + hrs; }
    if (min < 10) { min = '0' + min; }
    if (sec < 10) { sec = '0' + sec; }

    document.getElementById("clock").innerHTML = hrs + ':' + min + ':' + sec + ' ' + en;

    var days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    var months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    var dayName   = days[time.getDay()];
    var date      = time.getDate();
    var month     = months[time.getMonth()];
    var year      = time.getFullYear();

    var dateEl = document.getElementById("date");
    if (dateEl) {
        dateEl.innerHTML = dayName + ', ' + date + ' ' + month + ' ' + year;
    }
}