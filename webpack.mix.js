const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   // .vue()               // <-- hapus atau comment baris ini kalau ada
   // .sass('resources/sass/app.scss', 'public/css')  // hapus kalau error CSS
   // .postCss('resources/css/app.css', 'public/css') // hapus kalau ada
   .version();
