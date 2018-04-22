var utils = (function () {
    return {
		charCount: function (string_to_search, char_to_find) {
			if (!string_to_search || !char_to_find) { return 0; }
			var occurrences = string_to_search.toString().split(''),
				length = 0;
			for (var o = 0, occurrence; occurrence = occurrences[o]; o++) {
				if (occurrence === char_to_find.toString()) { length++; }
			}
			return length;
		},
		
		sum: function (objArray, fieldToSum) {
			var sum = 0;
			objArray.forEach(function (obj) {
				sum += Number(obj[fieldToSum]);
			});
			return sum;
		},

        time: {
			getObjectFromTimeString: function (timeString) {
				var timeObj = {};
				if (timeString) {
					var time_hour = timeString.split(":")[0],
						time_minutes = timeString.split(":")[1].split(" ")[0],
						time_of_day = timeString.split(" ")[1];
					timeObj["TimeHour"] = time_hour;
					timeObj["TimeMinutes"] = time_minutes;
					timeObj["TimeOfDay"] = time_of_day;
				}
				return timeObj;
			},
            validateTime: function (timeObj) {
                if (!timeObj.hasOwnProperty("time_hour") || !timeObj.hasOwnProperty("time_minutes") || !timeObj.hasOwnProperty("time_of_day")) {
                    return "Invalid Time Object";
                }

                var timeHourIsValid = utils.time.validateTimeHour(timeObj.time_hour),
                    timeMinutesIsValid = utils.time.validateTimeMinutes(timeObj.time_minutes),
                    timeOfDayIsValid = utils.time.validateTimeOfDay(timeObj.time_of_day),
                    errorString = "";

                if (timeHourIsValid === true && timeMinutesIsValid === true && timeOfDayIsValid === true) { return true; }

                if (timeHourIsValid !== true)
                {
                    errorString = utils.stringBuilder(errorString, timeHourIsValid + "\n");
                }
                if (timeMinutesIsValid !== true)
                {
                    errorString = utils.stringBuilder(errorString, timeMinutesIsValid + "\n");
                }
                if (timeOfDayIsValid !== true)
                {
                    errorString = utils.stringBuilder(errorString, timeOfDayIsValid);
                }

                return {
                          errorTitle: "Invalid Time Error!",
                          errorContent: errorString
                      };
            },
            validateTimeHour: function (time_hour) {
                if (time_hour.toString().trim().length === 0 || parseInt(time_hour) < 1 || parseInt(time_hour) > 12) {
                    return "Time hour must be between 01 and 12";
                } else {
                    return true;
                }
            },
            validateTimeMinutes: function (time_minutes) {
                if (time_minutes.toString().trim().length === 0 || parseInt(time_minutes) > 59) {
                    return "Time minutes must be between 00 and 59";
                } else {
                    return true;
                }
            },
            validateTimeOfDay: function (time_of_day) {
                if (time_of_day.toString().trim().length === 0) {
                    return "Select a time of day: AM or PM";
                } else {
                    return true;
                }
            }
        },

        userLogIns: {
            loggedInUser: null,
            generateRandomPassword: function (maxLength) {
              //giving credit... https://stackoverflow.com/questions/1349404/generate-random-string-characters-in-javascript
              var text = "",
                    possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

              for (var i = 0; i < Math.floor(Math.random() * maxLength); i++) {
                  do {
                      text += possible.charAt(Math.floor(Math.random() * possible.length));     
                  } while (text.length < 10);
              }                   
              return text;
            }
        },

        formattedDate: function (date) {
            var dateTime = new Date(date);
            dateTime = moment(dateTime).locale('en').format("ddd MMM DD YYYY h:mm A");
            return dateTime;
        },

        jsonTryParse: function (data) {
            var testParse;
            try {
              testParse = JSON.parse(data);
              return true;
            } catch (exc) {
              return false;
            }
          },

        getSortedDates: function (object_to_sort, bool_desc) {
            if  (!object_to_sort) { return; }
            
            var sortDescending;
            if (bool_desc && bool_desc === true) { sortDescending = true; }
            
            var sortedByDate = object_to_sort.sort(function(a,b) {                
                if (sortDescending) {
                    return new Date(b._dateTimeStamp) - new Date(a._dateTimeStamp);
                } else {
                   return new Date(a._dateTimeStamp) - new Date(b._dateTimeStamp);
                }
            });
            
            return sortedByDate;
        },

        isPastDate: function (date_to_process) {
            var processDate = new Date (date_to_process),  currentDate = new Date ();
            return processDate.getTime() < currentDate.getTime();
        },

        getBooleanValueAsInteger: function (value) {
            return (value && value === true) ? 1 : 0;            
        },
        
        completeAddress: function (address, city, state, zip) {
            if (address && city && state && zip) {
                if (city && state) {
                    return address + " " + city + ", " + state + " " + zip;
                }                
            } else {
                return "No address available";
            }
        },

        fullName: function (first_name, last_name, email) {
            if (first_name.length > 0 && last_name.length > 0) {
                return last_name + ", " + first_name;
            } else if (first_name.length > 0) {
                return first_name;
            } else if (last_name.length > 0) {
                return last_name;
            } else {
                return email;
            }
        },

        accountModifiedErrorObj: function (location) {
            return {
                      debug: location,
                      errorTitle: "Please log-in again",
                      errorContent: "Hi there. You may have been logged out due to inactivity. Or, an Admin User may have made changes to your account. We need you to log back in again."
                  };
        },

        async: function (php_file, post_data, errorCallBack, callBack) {
            $.post(php_file, post_data)
              .then(function (result) {
                  if (callBack && callBack(result));
              })
              .fail(function (error) {
                  if (errorCallBack && errorCallBack(error));
              });
        },

        parsedColumnName: function (name) {
            var delimitedByUnderscore = name.split("_"), 
                  delimitedByUpperCase = utils.getUpperCaseArray(name);
            
            if (delimitedByUnderscore.length === 1 && delimitedByUpperCase.length === 1) {
                return name;
            } else if (delimitedByUnderscore.length > 1) {
                return delimitedByUnderscore.join(" ");
            } else {
                return utils.getUpperCaseArray(name).join(" ");
            }
        },

        scrollNotesToEnd: function (element_id) {
            setTimeout(function () {
                var element = document.getElementById(element_id);
                element.scrollTop = element.scrollHeight;
            }, 1);
        },

        getUpperCaseArray: function (name) {
          /*giving credit: https://stackoverflow.com/questions/7888238/javascript-split-string-on-uppercase-characters*/
            var result = name.replace(/([A-Z]+)/g, ",$1").replace(/^,/, "");
            return result.split(",");
        },

        getCurrentDate: function () {
            /*giving credit: https://stackoverflow.com/questions/8305259/check-if-date-is-in-the-past-javascript*/
            var now = new Date();
            now.setHours(0,0,0,0);
            return now;
        },

        getCurrentDateTimeStamp: function () {
            /*giving credit: https://stackoverflow.com/questions/1531093/how-do-i-get-the-current-date-in-javascript*/
            /*giving credit: https://www.w3schools.com/jsref/jsref_tolocaletimestring.asp*/
            var date = new Date(),
                dd = date.getDate(),
                mm = date.getMonth() + 1,
                yyyy = date.getFullYear(),
                time = date.toLocaleTimeString(),
                dateTimeStamp = "";

            if(dd<10) {
                dd = '0'+dd
            }

            if(mm<10) {
                mm = '0'+mm
            }

            dateTimeStamp = mm + '/' + dd + '/' + yyyy + ' ' + time;

            return dateTimeStamp;
        },

        isValidEmailAddress: function (email) {
            var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;  
            if (reg.test(email) == false)
            {
                return false;
            }  
            return true;
        },

        isValidPassword: function (password) {
            /* giving credit... http://www.thegeekstuff.com/2008/06/the-ultimate-guide-for-creating-strong-passwords */
            var ucAlphaCount = 0, lcAlphaCount = 0, numberCount = 0, specialCount = 0,
                parsedPwd = password.split(''), specialChars = ['!', '@', '#', '$', '%', '&', '_'];

            //0. 8 characters in length
            if (password.toString().trim().length < 8) { return false; }
            //1. at least one lower case alphabet
            //2. at least one upper case alphabet
            //3. at least one number
            //4. at least one special character
            parsedPwd.forEach(function (character) {
                if (character === character.toUpperCase()) { ucAlphaCount++; }
                if (character === character.toLowerCase()) { lcAlphaCount++; }
                if (!isNaN(character)) { numberCount++; }
                if (specialChars.indexOf(character) > -1) { specialCount++; }
            });
            return (ucAlphaCount > 0 && lcAlphaCount > 0 && numberCount > 0 && specialCount > 0);
        },

        cloneObject: function (object_to_clone) {
            var clone = {}, keys = _.keys(object_to_clone);
            for (var k = 0; k < keys.length; k++) {
                var key = keys[k];
                clone[key] = object_to_clone[key];
            }
            return clone;
        },

        getValidSelector: function (selectorId) {
            if ($(selectorId).length > 0) { return selectorId; }
            if ($("#" + selectorId).length > 0) { return "#" + selectorId; }
            return undefined;
        },

        stringBuilder: function (string, appendText, opt_appendSpace) {
            if (opt_appendSpace && opt_appendSpace === true) {
                return string = string + " " + appendText;
            }
            return string += appendText;
        },

        _export: {
          //giving credit... https://medium.com/@danny.pule/export-json-to-csv-file-using-javascript-a0b7bc5b00d2
          convertToCSV: function (objArray) {
            var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray,
                str = '';

            for (var i = 0; i < array.length; i++) {
                var line = '';
                for (var index in array[i]) {
                    if (line != '') line += ','

                    line += array[i][index];
                }

                str += line + '\r\n';
            }

            return str;
          },
          exportCSVFile: function (headers, items, fileTitle) {
            items.forEach(function (item) {
                for (var h = 0, header; header = headers[h]; h++) {
                    item[header].replace(/,/g, '');
                }
            });

            if (headers) {
                items.unshift(headers);
            }

            // Convert Object to JSON
            var jsonObject = JSON.stringify(items),
                csv = this.convertToCSV(jsonObject),
                exportedFilenmae = fileTitle + '.csv' || 'export.csv',
                blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            if (navigator.msSaveBlob) { // IE 10+
                navigator.msSaveBlob(blob, exportedFilenmae);
            } else {
                var link = document.createElement("a");
                if (link.download !== undefined) { // feature detection
                    // Browsers that support HTML5 download attribute
                    var url = URL.createObjectURL(blob);
                    link.setAttribute("href", url);
                    link.setAttribute("download", exportedFilenmae);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            }
          }
        },
        print: {
            setPrintElement: function (elementId, printWindow, callBackFunc) {
                var css = "<style>@page { size: landscape; } .hide-on-print { visibility: hidden; } .center-on-print { text-align: center; }</style>";
                $.get("../ChurchScheduler/libs/bootstrap-4.0.0/css/bootstrap.css")
                    .then(function (file_content) {
                        css = utils.stringBuilder(css, '<style>', true);
                        css = utils.stringBuilder(css, file_content);
                        css = utils.stringBuilder(css, '</style>');
                        $.get("../ChurchScheduler/css/app.css")
                            .then(function (file_content) {
                                        css = utils.stringBuilder(css, '<style>', true);
                                        css = utils.stringBuilder(css, file_content);
                                        css = utils.stringBuilder(css, '</style>');
                                    })
                                    .then(function () {
                                        var $el = $(elementId),
                                        html = "<!DOCTYPE HTML>";
                                        html += '<html lang="en-us">';
                                        html += '<head>' + css + '</head>';
                                        html += '<title>Church Visitation Scheduler | Print</title>';
                                        html += "<body>";
                                        html += $el.html();
                                        html += "</body>";
                                        printWindow.document.write(html);
                                    })
                                    .done(function () {
                                        if (callBackFunc && callBackFunc());
                                    });;

                    })
                    .fail(function (err) {
                        console.log(err);
                    });
            },
            printPage: function (elementId) {
                elementId = utils.getValidSelector(elementId);
                if (!elementId) { return; }
                /* giving credit... https://stackoverflow.com/questions/242182/how-can-i-pop-up-a-print-dialog-box-using-javascript */
                var printWindow = window.open("about:blank", "_blank", "menubar=0,location=0,toolbar=0,resizable=0,status=0,scrollbars=1");
                if (!printWindow) { return "You must enable pop-ups to use this feature." };
                printWindow.document.title = "Church Visitation Scheduler | Print";
                utils.print.setPrintElement(elementId, printWindow, function () {
                    utils.print.openPageForPrinting(printWindow);
                });
            },
            openPageForPrinting: function (printWindow) {
                printWindow.window.print();
                printWindow.document.close();
            }
        }
      };
})();

