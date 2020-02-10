window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {
}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.interceptors.response.use(
    function (response) {
        return response;
    },
    function (error) {
        if (error.response) {
            // 用户未登录, 跳转到登录页面
            if (error.response.status == 401) {
                swal({
                    title: "未登录",
                    icon: "info"
                }).then(function () {
                    window.location = "/login";
                });
            } else if (error.response.status == 422 && error.response.data.errors) {
                let errors = error.response.data.errors;
                let msgArr = [];
                for (var i1 in errors) {
                    for (var i2 in errors[i1]) {
                        msgArr.push(errors[i1][i2]);
                    }
                }

                let msg = "<div>" + msgArr.join("<br>") + "</div>";

                swal({
                    title: "出错",
                    content: $(msg)[0],
                    icon: "error",
                });
            } else {
                swal({
                    // title: "发生错误(" + error.response.status + ")",
                    title: "错误",
                    text: error.response.data.message,
                    icon: "error"
                })
            }
        } else {
            swal({
                title: "未知错误",
                text: error.message,
                icon: "error"
            })
        }

        return Promise.reject(error);
    });

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     encrypted: true
// });

require('sweetalert');

window.addressData = require('china-area-data/v4/data');
