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
                }
            }, function(res) {

            var collectedBody = '';
            res.on('data', function (body) {
                collectedBody += body;
            });
            res.on('error', function(e) {
                errorCallback('response error', URL);
            });
            res.on('abort', function(e) {
                errorCallback('abort', URL);
            });
            res.on('end', function() {
                callback(collectedBody, res.statusCode, URL);
            });

        });
        req.on('error', function(e) {
            errorCallback(e, URL);
        });
        req.setTimeout(1000, function() {
            var error = new Error('Time out');
            error.code = timeoutErrorCode;
            errorCallback(error, URL);
        });
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

https.globalAgent.maxSockets = 50; // may need to modify this to suite your computers setup and resources

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

    var numUrls = urlQueue.length,
        numPushes = 0,
        pushesLeft = function() {
            return numUrls - numPushes;
        },
        percentLeft = function() {
            return Math.round( (pushesLeft() / numUrls) * 1000 ) / 10;
        };


    var sendInterval = setInterval(function() {

        for( var i=0; i < 50; i++ ) {

            if( urlQueue.length == 0 ) {
                clearInterval(sendInterval);
            }
            else {

                var onError = function(err, url, putBackInQueue) {
                        out('*** :( Failed pushing '+url.trim()+' due to '+err.message+' ('+pushesLeft()+' - '+percentLeft()+'% pushes left)');
                        if( putBackInQueue !== false ) {
                            // Put url back in queue
                            urlQueue.push(url);
                        } else {
                            numPushes++;
                        }
                    },
                    onSuccess = function(body, status, url) {

                        if( status == 200 ) {
                            out('* Pushed '+url+' successfully ('+pushesLeft()+' - '+percentLeft()+'% pushes left)');
                            numPushes++;
                        } else {
                            onError(new Error('Server returned status '+status+' (body: '+body+')'), url, false);
                        }
                    },

                // take one url out of the queue
                url = urlQueue.splice(0, 1)[0];

                // ping cxense
                pingcXense(url, onSuccess, onError);

                if( urlQueue.length == 0 ) {
                    clearInterval(sendInterval);
                    break;
                }
            }
        }

    }, 250);

});
