/**
 * Fichier de compatibilité JavaScript pour assurer le support
 * des navigateurs modernes et anciens
 * Compatible avec: Chrome 60+, Firefox 55+, Safari 12+, Edge 79+, IE 11
 */

// Polyfills pour les navigateurs anciens
(function() {
    'use strict';

    // Polyfill pour Array.from (IE 11)
    if (!Array.from) {
        Array.from = function(arrayLike, mapFn, thisArg) {
            var C = this;
            var items = Object(arrayLike);
            if (arrayLike == null) {
                throw new TypeError('Array.from requires an array-like object - not null or undefined');
            }
            var mapFunction = mapFn === undefined ? undefined : mapFn;
            var T;
            if (typeof mapFunction !== 'undefined') {
                if (typeof mapFunction !== 'function') {
                    throw new TypeError('Array.from: when provided, the second argument must be a function');
                }
                if (arguments.length > 2) {
                    T = thisArg;
                }
            }
            var len = parseInt(items.length);
            var A = typeof C === 'function' ? Object(new C(len)) : new Array(len);
            var k = 0;
            var kValue;
            while (k < len) {
                kValue = items[k];
                if (mapFunction) {
                    A[k] = typeof T === 'undefined' ? mapFunction(kValue, k) : mapFunction.call(T, kValue, k);
                } else {
                    A[k] = kValue;
                }
                k += 1;
            }
            A.length = len;
            return A;
        };
    }

    // Polyfill pour Object.assign (IE 11)
    if (typeof Object.assign !== 'function') {
        Object.assign = function(target) {
            if (target == null) {
                throw new TypeError('Cannot convert undefined or null to object');
            }
            var to = Object(target);
            for (var index = 1; index < arguments.length; index++) {
                var nextSource = arguments[index];
                if (nextSource != null) {
                    for (var nextKey in nextSource) {
                        if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
                            to[nextKey] = nextSource[nextKey];
                        }
                    }
                }
            }
            return to;
        };
    }

    // Polyfill pour String.prototype.includes (IE 11)
    if (!String.prototype.includes) {
        String.prototype.includes = function(search, start) {
            if (typeof start !== 'number') {
                start = 0;
            }
            if (start + search.length > this.length) {
                return false;
            } else {
                return this.indexOf(search, start) !== -1;
            }
        };
    }

    // Polyfill pour Array.prototype.includes (IE 11)
    if (!Array.prototype.includes) {
        Array.prototype.includes = function(searchElement) {
            return this.indexOf(searchElement) !== -1;
        };
    }

    // Polyfill pour Element.closest (IE 11)
    if (!Element.prototype.closest) {
        Element.prototype.closest = function(s) {
            var el = this;
            do {
                if (Element.prototype.matches.call(el, s)) return el;
                el = el.parentElement || el.parentNode;
            } while (el !== null && el.nodeType === 1);
            return null;
        };
    }

    // Polyfill pour Element.matches (IE 11)
    if (!Element.prototype.matches) {
        Element.prototype.matches = Element.prototype.msMatchesSelector || 
                                    Element.prototype.webkitMatchesSelector;
    }

    // Polyfill pour CustomEvent (IE 11)
    if (typeof window.CustomEvent !== 'function') {
        function CustomEvent(event, params) {
            params = params || { bubbles: false, cancelable: false, detail: null };
            var evt = document.createEvent('CustomEvent');
            evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
            return evt;
        }
        window.CustomEvent = CustomEvent;
    }

    // Polyfill pour fetch API (IE 11)
    if (!window.fetch) {
        window.fetch = function(url, options) {
            return new Promise(function(resolve, reject) {
                var xhr = new XMLHttpRequest();
                options = options || {};
                
                xhr.open(options.method || 'GET', url);
                
                if (options.headers) {
                    for (var header in options.headers) {
                        xhr.setRequestHeader(header, options.headers[header]);
                    }
                }
                
                xhr.onload = function() {
                    resolve({
                        ok: xhr.status >= 200 && xhr.status < 300,
                        status: xhr.status,
                        statusText: xhr.statusText,
                        text: function() {
                            return Promise.resolve(xhr.responseText);
                        },
                        json: function() {
                            return Promise.resolve(JSON.parse(xhr.responseText));
                        }
                    });
                };
                
                xhr.onerror = function() {
                    reject(new Error('Network error'));
                };
                
                xhr.send(options.body || null);
            });
        };
    }

    // Polyfill pour Promise (IE 11)
    if (typeof Promise === 'undefined') {
        window.Promise = function(executor) {
            var self = this;
            self.state = 'pending';
            self.value = undefined;
            self.handlers = [];

            function resolve(result) {
                if (self.state === 'pending') {
                    self.state = 'fulfilled';
                    self.value = result;
                    self.handlers.forEach(handle);
                    self.handlers = null;
                }
            }

            function reject(error) {
                if (self.state === 'pending') {
                    self.state = 'rejected';
                    self.value = error;
                    self.handlers.forEach(handle);
                    self.handlers = null;
                }
            }

            function handle(handler) {
                if (self.state === 'pending') {
                    self.handlers.push(handler);
                } else {
                    if (self.state === 'fulfilled' && typeof handler.onFulfilled === 'function') {
                        handler.onFulfilled(self.value);
                    }
                    if (self.state === 'rejected' && typeof handler.onRejected === 'function') {
                        handler.onRejected(self.value);
                    }
                }
            }

            this.then = function(onFulfilled, onRejected) {
                return new Promise(function(resolve, reject) {
                    handle({
                        onFulfilled: function(result) {
                            try {
                                resolve(onFulfilled ? onFulfilled(result) : result);
                            } catch (ex) {
                                reject(ex);
                            }
                        },
                        onRejected: function(error) {
                            try {
                                resolve(onRejected ? onRejected(error) : error);
                            } catch (ex) {
                                reject(ex);
                            }
                        }
                    });
                });
            };

            executor(resolve, reject);
        };
    }

})();

// Utilitaires de compatibilité
window.CompatibilityUtils = {
    
    // Vérifier le support des fonctionnalités
    checkFeatureSupport: function() {
        return {
            flexbox: this.supportsFlexbox(),
            grid: this.supportsGrid(),
            customProperties: this.supportsCustomProperties(),
            fetch: typeof fetch !== 'undefined',
            promises: typeof Promise !== 'undefined',
            arrow_functions: this.supportsArrowFunctions(),
            template_literals: this.supportsTemplateLiterals()
        };
    },

    // Vérifier le support de Flexbox
    supportsFlexbox: function() {
        var div = document.createElement('div');
        div.style.display = 'flex';
        return div.style.display === 'flex';
    },

    // Vérifier le support de CSS Grid
    supportsGrid: function() {
        var div = document.createElement('div');
        div.style.display = 'grid';
        return div.style.display === 'grid';
    },

    // Vérifier le support des variables CSS
    supportsCustomProperties: function() {
        return window.CSS && CSS.supports && CSS.supports('color', 'var(--test)');
    },

    // Vérifier le support des fonctions fléchées
    supportsArrowFunctions: function() {
        try {
            eval('() => {}');
            return true;
        } catch (e) {
            return false;
        }
    },

    // Vérifier le support des template literals
    supportsTemplateLiterals: function() {
        try {
            eval('`template`');
            return true;
        } catch (e) {
            return false;
        }
    },

    // Ajouter des classes CSS selon le support
    addFeatureClasses: function() {
        var html = document.documentElement;
        var features = this.checkFeatureSupport();
        
        for (var feature in features) {
            if (features[feature]) {
                html.classList.add('supports-' + feature.replace('_', '-'));
            } else {
                html.classList.add('no-' + feature.replace('_', '-'));
            }
        }
    },

    // Fallback pour addEventListener (IE 8)
    addEvent: function(element, event, handler) {
        if (element.addEventListener) {
            element.addEventListener(event, handler, false);
        } else if (element.attachEvent) {
            element.attachEvent('on' + event, handler);
        }
    },

    // Fallback pour removeEventListener (IE 8)
    removeEvent: function(element, event, handler) {
        if (element.removeEventListener) {
            element.removeEventListener(event, handler, false);
        } else if (element.detachEvent) {
            element.detachEvent('on' + event, handler);
        }
    }
};

// Initialisation automatique
document.addEventListener('DOMContentLoaded', function() {
    CompatibilityUtils.addFeatureClasses();
});

// Export pour les modules (si supporté)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CompatibilityUtils;
}
