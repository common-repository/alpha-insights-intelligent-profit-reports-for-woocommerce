/*
BSD 2-Clause License

Copyright (c) 2016, Benjamin Cordier
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this
  list of conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice,
  this list of conditions and the following disclaimer in the documentation
  and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

@source https://github.com/greenstick/interactor
*/

var Interactor = function (config) {
    // Call Initialization on Interactor Call
    this.__init__(config);
};

function wpdMicroTime() {
    var timestamp = Math.floor(new Date().getTime() / 1000)
    return timestamp;
}

Interactor.prototype = {

    // Initialization
    __init__: function (config) {

        var interactor = this;
        
        // Argument Assignment          // Type Checks                                                                          // Default Values
        interactor.interactions       = typeof(config.interactions)               == "boolean"    ? config.interactions        : true,
        interactor.interactionElement = typeof(config.interactionElement)         == "string"     ? config.interactionElement :'interaction',
        interactor.interactionEvents  = Array.isArray(config.interactionEvents)   === true        ? config.interactionEvents  : ['mouseup', 'touchend'],
        interactor.conversions        = typeof(config.conversions)                == "boolean"    ? config.conversions        : true,
        interactor.conversionElement  = typeof(config.conversionElement)          == "string"     ? config.conversionElement  : 'conversion',
        interactor.conversionEvents   = Array.isArray(config.conversionEvents)    === true        ? config.conversionEvents   : ['mouseup', 'touchend'],
        interactor.endpoint           = typeof(config.endpoint)                   == "string"     ? config.endpoint           : '/interactions',
        interactor.async              = typeof(config.async)                      == "boolean"    ? config.async              : true,
        interactor.debug              = typeof(config.debug)                      == "boolean"    ? config.debug              : true,
        interactor.records            = [],
        interactor.session            = {},
        interactor.loadTime           = wpdMicroTime();
        
        // Initialize Session
        interactor.__initializeSession__();
        // Call Event Binding Method
        interactor.__bindEvents__();
        
        return interactor;
    },

    // Create Events to Track
    __bindEvents__: function () {
        
        var interactor  = this;

        // Set Interaction Capture
        if (interactor.interactions === true) {
            for (var i = 0; i < interactor.interactionEvents.length; i++) {
                document.querySelector('body').addEventListener(interactor.interactionEvents[i], function (e) {
                    e.stopPropagation();
                    if (e.target.classList.value === interactor.interactionElement) {
                        interactor.__addInteraction__(e, "interaction");
                    }
                });
            }   
        }

        // Set Conversion Capture
        if (interactor.conversions === true) {
            for (var i = 0; i < interactor.conversionEvents.length; i++) {
                document.querySelector('body').addEventListener(interactor.conversionEvents[i], function (e) {
                    e.stopPropagation();
                    if (e.target.classList.value === interactor.conversionElement) {
                        interactor.__addInteraction__(e, "conversion");
                    }
                });
            }   
        }

        // Bind onbeforeunload Event
        window.onbeforeunload = function (e) {
            interactor.__sendInteractions__();
        };
        
        return interactor;
    },

    // Add Interaction Object Triggered By Events to Records Array
    __addInteraction__: function (e, type) {
            
        var interactor  = this,

            // Interaction Object
            interaction     = {
                type            : type,
                event           : e.type,
                targetTag       : e.target.nodeName,
                targetClasses   : e.target.className,
                content         : e.target.innerText,
                clientPosition  : {
                    x               : e.clientX,
                    y               : e.clientY
                },
                screenPosition  : {
                    x               : e.screenX,
                    y               : e.screenY
                },
                createdAt       : wpdMicroTime()
            };
        
        // Insert into Records Array
        interactor.records.push(interaction);

        // Log Interaction if Debugging
        if (interactor.debug) {
            // Close Session & Log to Console
            interactor.__closeSession__();
            console.log("Session:\n", interactor.session);
        }

        return interactor;
    },

    // Generate Session Object & Assign to Session Property
    __initializeSession__: function () {
        var interactor = this;

        // Assign Session Property
        interactor.session  = {
            loadTime        : interactor.loadTime,
            unloadTime      : wpdMicroTime(),
            language        : window.navigator.language,
            platform        : window.navigator.platform,
            userid 			: wpd_ai_session_vars.user_id,
            port            : window.location.port,
            clientStart     : {
                name            : window.navigator.appVersion,
                innerWidth      : window.innerWidth,
                innerHeight     : window.innerHeight,
                outerWidth      : window.outerWidth,
                outerHeight     : window.outerHeight
            },
            page            : {
                location        : window.location.pathname,
                href            : window.location.href,
                origin          : window.location.origin,
                title           : document.title,
                pageid 			: wpd_ai_session_vars.page_id
            },
            endpoint        : interactor.endpoint
        };

        return interactor;
    },

    // Insert End of Session Values into Session Property
    __closeSession__: function () {

        var interactor = this;

        // Assign Session Properties
        interactor.session.unloadTime   = wpdMicroTime();
        interactor.session.interactions = interactor.records;
        interactor.session.clientEnd    = {
            name            : window.navigator.appVersion,
            innerWidth      : window.innerWidth,
            innerHeight     : window.innerHeight,
            outerWidth      : window.outerWidth,
            outerHeight     : window.outerHeight
        };

        return interactor;
    },


    // Gather Additional Data and Send Interaction(s) to Server
    __sendInteractions__: function () {
        
        var interactor  = this,
            // Initialize Cross Header Request
            xhr         = new XMLHttpRequest();
            
        // Close Session
        interactor.__closeSession__();

        // Post Session Data Serialized as JSON
        xhr.open('POST', interactor.endpoint, interactor.async);
        xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
        xhr.send(JSON.stringify(interactor.session));

        return interactor;
    }

};

// Mobile Detection Method
window.ismobile = function() {
    var mobile = false;
    (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
    return mobile;
};

// Setup Events by Device Type
if (window.ismobile()) {
    var interactionEventsArray = ["touchend"],
        conversionEventsArray = ["touchstart"];
} else {
    var interactionEventsArray = ["mouseup"],
        conversionEventsArray = ["mousedown"];
}

// Initialize Interactor
var interactor = new Interactor({
    interactions            : true,
    interactionElement      : "interaction",
    interactionEvents       : interactionEventsArray,
    conversions             : true,
    conversionElement       : "conversion",
    conversionEvents        : conversionEventsArray,
    endpoint                : '/wp-json/alpha-insights/v1/user-tracking',
    async                   : true,
    debug                   : true
});

// Empty Data Model for VM Initialization
var model       = {
    interactions    : [
        {
            type            : "",
            event           : "",
            targetTag       : "",
            targetClasses   : "",
            content         : "",
            clientPosition  : {
                x               : 0,
                y               : 0
            },
            screenPosition  : {
                x               : 0,
                y               : 0
            },
            createdAt       : ""
        }
    ],
    conversions     : [
        {
            type            : "",
            event           : "",
            targetTag       : "",
            targetClasses   : "",
            content         : "",
            clientPosition  : {
                x               : 0,
                y               : 0
            },
            screenPosition  : {
                x               : 0,
                y               : 0
            },
            createdAt        : ""
        }
    ],
    loadTime        : "",
    unloadTime      : "",
    language        : "",
    platform        : "",
    port            : "",
    clientStart     : {
        name            : "",
        innerWidth      : 0,
        innerHeight     : 0,
        outerWidth      : 0,
        outerHeight     : 0
    },
    clientEnd       : {
        name            : "",
        innerWidth      : 0,
        innerHeight     : 0,
        outerWidth      : 0,
        outerHeight     : 0
    },
    page            : {
        location        : "",
        href            : "",
        origin          : "",
        title           : ""
    },
    endpoint        : ""
};