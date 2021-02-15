jQuery(document).ready(function () {
    if (undefined !== window.localStorage.getItem('drivingMatrix')
        && null !== window.localStorage.getItem('drivingMatrix')) {
        let matrix = JSON.parse(window.localStorage.getItem('drivingMatrix'));
        let time = secondsToTime(matrix.durations[0][0]);
     //   let distance = Math.ceil(matrix.destinations[0].distance);
        jQuery('#address').text(`P.O ${window.localStorage.getItem('USER_POSTCODE')}, ${matrix.destinations[0].name}`);
        jQuery('#driving').text(`${time} ETA`);
    }

    function secondsToTime(secs) {
        var hours = Math.floor(secs / (60 * 60));

        var divisor_for_minutes = secs % (60 * 60);
        var minutes = Math.floor(divisor_for_minutes / 60);

        var divisor_for_seconds = divisor_for_minutes % 60;
        var seconds = Math.ceil(divisor_for_seconds);

        var obj = {
            "h": hours,
            "m": minutes,
            "s": seconds
        };
        return `${obj.h}:${obj.m}`;
        //return obj;
    }
    async function postData(url = '', data = {}) {

        const response = await fetch(url, {
            method: 'POST',
            mode: 'same-origin',
            cache: 'default',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            redirect: 'follow',
            referrerPolicy: 'no-referrer',
            body: data
        });
        const status = await response.json();
        return status;
    }

    async function jPostData(url, data) {
        let posting = jQuery.post(url, data);
        return posting;
    }

    window.saveUserDrivingMatrix = async (postcode, matrix) => {
        loader.style.display = 'inline-block';
        let data = {
            'action': 'save_user_driving_matrix',
            'postcode': postcode,
            'distance': matrix.destinations[0].distance,
            'address': matrix.destinations[0].name,
            'duration': matrix.durations[0][0],
            'longitude': matrix.destinations[0].location[0],
            'latitude': matrix.destinations[0].location[1]
        };
        const posting = jPostData(jebStoresAjax.ajaxurl, data);
        try {
            posting.done(function (response) {
                window.localStorage.setItem('SESSION_SAVED', reponse);
            });
        } catch (e) {

        }
        window.localStorage.setItem('USER_POSTCODE', postcode);
        loader.style.display = 'none';

        window.location.href = '/?post_type=product';


    }
});
