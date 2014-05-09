/**
 * This is a script that can be used to register a bunch of URL:s at cxense
 *
 * 1. Create a file in this directory named urlQueue.txt where you put all your URL:s separated
 * with a new line
 *
 * 2. Call this script from command line
 * $ node run.js api-user@mail.com api-key
 *
 */
var fs = require('fs'),
    https = require('https'),
    crypto = require('crypto'),
    querystring = require('querystring'),
    urlFile = __dirname+'/urls.txt',
    urlQueue = [],
    readline = require('readline'),
    timeoutErrorCode = 99001,
    onGoingRequests = 0,
    onGoingRequestsLimit = 200,
    cXenseAuthStr = null,
    pingcXense = function(URL, callback, errorCallback) {

        var sendBody = '{"url":"'+URL+'"}';

        var req = https.request({
                host : 'api.cxense.com',
                path : '/profile/content/push',
                port : 443,
                method : 'POST',
                write : sendBody,
                headers : {
                    'X-cXense-Authentication' : cXenseAuthStr,
                    'Content-Length' : Buffer.byteLength(sendBody, 'utf8')
                },
                timeout : 10000
            }, function(res) {

            var collectedBody = '';
            res.on('data', function (body) {
                collectedBody += body;
            });
            res.on('end', function() {
                callback(collectedBody, res.statusCode, URL);
            });

        });
        req.on('error', function(e) {
            errorCallback(e, URL);
        });
        if(req.connection != undefined) {
            req.connection.setTimeout(options.timeout, function() {
                var error = new Error('Time out');
                error.code = timeoutErrorCode;
                errorCallback(error, URL);
            });
        }
        req.write(sendBody);
        req.end();
    },
    rd = readline.createInterface({
        input: fs.createReadStream(urlFile),
        output: process.stdout,
        terminal: false
    }),
    out = function(str) {
        console.log(str);
    };

/*
 * Create authencation string
 */
var userName = process.argv[ process.argv.length - 2],
    apiKey = process.argv[ process.argv.length - 1],
    date = new Date().toISOString(),
    signature = crypto.createHmac('sha256', apiKey).update(date).digest('hex');

cXenseAuthStr = 'username=' +userName+ ' date=' +date+ ' hmac-sha256-hex='+signature;

/*
 * Load urls into memory, line-by-line
 */
out('- Parsing file '+urlFile);

rd.on('line', function(line) {
    if( line.indexOf('http') === 0 ) {
        urlQueue.push(line);
    }
});

rd.on('close', function() {

    /*
     * Start to send urls to cXense
     */
    out('- ' +urlQueue.length+ ' URL:s loaded into memory');

    var sendInterval = setInterval(function() {

        for( var i=0; i < 50; i++ ) {
            if( onGoingRequests > onGoingRequestsLimit ) {
                // To many simultaneous requests, pls wait a while...
                break;
            }
            else if( urlQueue.length == 0 ) {
                clearInterval(sendInterval);
            }
            else {

                onGoingRequests++;

                var onError = function(err, url, putBackInQueue) {
                        onGoingRequests--;
                        out('*** :( Failed pushing '+url.trim()+' due to '+err.message);
                        if( putBackInQueue !== false ) {
                            urlQueue.push(url);
                        }
                    },
                    onSuccess = function(body, status, url) {

                        if( status == 200 ) {
                            onGoingRequests--;
                            out('* Pushed '+url+' successfully');
                        } else {
                            onError(new Error('Server returned status '+status+' (body: '+body+')'), url, false);
                        }
                    },

                    // take one url out of the queue
                    url = urlQueue.splice(0, 1)[0];

                // ping cxense
                pingcXense(url, onSuccess, onError);
            }
        }

    }, 250);

});
