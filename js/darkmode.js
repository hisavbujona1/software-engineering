// Load Theme on Every Page

const darkToggle = document.getElementById("darkToggle");


// Check saved theme

if(localStorage.getItem("theme") === "dark"){

    document.body.classList.add("dark-mode");


    if(darkToggle){

        darkToggle.innerHTML = "☀ Light Mode";

    }

}





// Toggle Dark Mode

if(darkToggle){


darkToggle.addEventListener("click", function(){


    document.body.classList.toggle("dark-mode");



    if(document.body.classList.contains("dark-mode")){


        localStorage.setItem("theme","dark");


        darkToggle.innerHTML = "☀ Light Mode";


    }

    else{


        localStorage.setItem("theme","light");


        darkToggle.innerHTML = "🌙 Dark Mode";


    }



});


}

const menuToggle = document.getElementById("menuToggle");

const sidebar = document.getElementById("sidebar");


if(menuToggle && sidebar){


menuToggle.addEventListener("click",()=>{


sidebar.classList.toggle("active");


});


}