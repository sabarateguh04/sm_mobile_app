  $(document).ready(function() {
    cuaca();
  });

  function cuaca(params) {
    fetch('https://api.openweathermap.org/data/2.5/onecall?lat=-8.534196869112206&lon=115.32707956977748&appid=0aacff65b5e02f5d76fefb88183a1298')
      .then(res => res.json())
      .then(data => {
        var data_cuaca = []
        let cuaca_saat_ini = data.current
        let cuaca_fullday = data.daily;
        for (let index = 0; index < cuaca_fullday.length; index++) {
          data_cuaca.push({
            hari: moment.unix(cuaca_fullday[index].dt).format("DD MMMM, YYYY"),
            pagi: converKelvintoCelcius(cuaca_fullday[index].temp.morn) + " °C",
            siang: converKelvintoCelcius(cuaca_fullday[index].temp.day) + " °C",
            sore: converKelvintoCelcius(cuaca_fullday[index].temp.eve) + " °C",
            malam: converKelvintoCelcius(cuaca_fullday[index].temp.night) + " °C",
            kecepatan_angin: cuaca_fullday[index].wind_speed + " knot",
            cuaca: cuaca_fullday[index].weather[0].main,
            icon: `https://openweathermap.org/img/wn/${cuaca_fullday[index].weather[0].icon}@2x.png`,

          })
          // dt.innerHTML = hari;
        }
        for (let index = 0; index < data_cuaca.length; index++) {
          $('#hari').val(data_cuaca[0].hari);
          $('#icon').val(data_cuaca[0].icon);
          $('#cuaca').val(data_cuaca[0].cuaca);
        }
      })
  }

  function converKelvintoCelcius(params) {
    const rumus = params - 273.15;
    const pembulatan = rumus.toFixed(0)
    return pembulatan;
  }