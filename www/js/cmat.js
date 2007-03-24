CMAT = {
    ageGroupConversion: {
        1 : "Young Child",
        2 : "Child",
        3 : "Teen",
        4 : "Adult (Wushu)",
        5 : "Adult (Taiji)",
        6 : "Senior (Wushu)",
        7 : "Senior (Taiji)",
        8 : "18-35",
        9 : "36-52",
        10 : "53+"
    },

    levelConversion: {
        0 : "N/A",
        1 : "Beginner",
        2 : "Intermediate",
        3 : "Advanced"
    },

    genderConversion: {
        1 : "Male",
        2 : "Female",
        3 : "Combined"
    },

    formConversion: {
        1 : "Traditional Northern Style Hand Form",
        2 : "Traditional Southern Style Hand Form",
        3 : "Traditional Other Hand Form",
        4 : "Traditional Short Weapons",
        5 : "Traditional Long Weapons",
        6 : "Traditional Other Weapons",
        7 : "Contemporary Long Fist",
        8 : "Contemporary Southern Fist",
        9 : "Contemporary Other Hand Form",
        10 : "Contemporary Straightsword",
        11 : "Contemporary Broadsword",
        12 : "Contemporary Southern Broadsword",
        13 : "Contemporary Spear",
        14 : "Contemporary Staff",
        15 : "Contemporary Southern Staff",
        16 : "Contemporary Other Weapons",
        17 : "Nandu Chang Quan",
        18 : "Nandu Nan Quan",
        19 : "Nandu Jian Shu",
        20 : "Nandu Dao Shu",
        21 : "Nandu Nan Dao",
        22 : "Nandu Qiang Shu",
        23 : "Nandu Gun Shu",
        24 : "Nandu Nan Gun",
        25 : "Internal 42-Form Taijiquan",
        26 : "Internal 24-Form Yang",
        27 : "Internal 24-Form Chen",
        28 : "Internal Open Yang",
        29 : "Internal Open Chen",
        30 : "Internal Xingyiquan",
        31 : "Internal Baguazhang",
        32 : "Internal Sun Taiji",
        33 : "Internal Guang-Ping",
        34 : "Internal Other Hand Forms",
        35 : "Internal 42-Form Taiji Straightsword",
        36 : "Internal Taiji Sword",
        37 : "Internal Other Taiji Weapons",
        38 : "Internal Other Weapons",
        39 : "External Group Set",
        40 : "Internal Group Set",
        41 : "Guang-Ping Group Set",
        42 : "Sparring Group Set",
        43 : "Push Hands - Male < 145 lbs",
        44 : "Push Hands - Male 145-175 lbs",
        45 : "Push Hands - Male 176-205 lbs",
        46 : "Push Hands - Male 205+ lbs",
        47 : "Push Hands - Female <135 lbs",
        48 : "Push Hands - Female 135+ lbs"
    },

    formatCompetitorId: function (id) {
        return "CMAT15-" + id;
    },

    formatGroupId: function (id) {
        return "G-" + id;
    },

    formatAgeGroupId: function (id) {
        return this.ageGroupConversion[id];
    },

    formatLevelId: function (id) {
        return this.levelConversion[id];
    },

    formatGenderId: function (id) {
        return this.genderConversion[id];
    },

    formatFormId: function (id) {
        return this.formConversion[id];
    },

    formatFullName: function (first, last) {
        return last + ", " + first;
    },

    makeEventSearchString: function (event){
        var e = event.form_blowout[0];
				
	if(event.form_blowout.length==0)return "";
		var result = [
			event.event_code,
			event.event_id.toString(),
			CMAT.formatAgeGroupId(e.age_group_id), 
			CMAT.formatGenderId(e.gender_id), 
			CMAT.formatLevelId(e.level_id), 
			CMAT.formatFormId(e.form_id)
			];
		//console.debug(result);
		//console.debug(result);
		return result.join(" ");
    },

    makeCompetitorSearchString: function (e){
        var result = [
            e.first_name,
            e.last_name
        ];
        return result.join(" ");
    },

    getTimeLimits: function (levelId, ageGroupId, formId) {
        var ret = [null, null];
        if (1 <= formId && formId <= 6) {
            // Traditional
            if (1 == ageGroupId || 2 == ageGroupId) {
                // <7, 8-12
                ret = [30, 120];
            } else if (3 <= ageGroupId && ageGroupId <= 10) {
                // 13-17, Adult, Senior
                if (1 == levelId || 2 == levelId) {
                    // Beginner, Intermediate
                    ret = [30, 120];
                } else if (3 == levelId) {
                    // Advanced
                    ret = [45, 120];
                }
            }
        } else if (7 <= formId && formId <= 16) {
            // Contemporary
            if (1 == levelId) {
                // Beginners
                ret = [30, null];
            } else if (2 == levelId){
                // Intermediate
                ret = [60, null];
            } else if (3 == levelId) {
                // Advanced
                if (9 == formId || 16 == formId) {
                    // Contemporary Other Hand Form, Contemporary Other Weapon
                    ret = [60, null];
                } else {
                    ret = [80, null];
                }
            }
        } else if (17 <= formId && formId <= 24) {
            // Nandu
            ret = [80, null];
        } else if (25 <= formId && formId <= 38) {
            // Internal
            if (26 == formId || 27 == formId) {
                // 24 Form Chen, 24 Form Yang
                ret = [240, 300];
            } else if (35 == formId) {
                // 42 Form Taiji Straightsword
                ret = [180, 240];
            } else if (25 == formId) {
                // 42 Form Taijiquan
                ret = [300, 600];
            } else if (31 == formId) {
                // Bagua Zhang
                ret = [60, 120];
            } else if (28 == formId || 29 == formId || 32 == formId
                || 33 == formId || 34 == formId) {
                // Guangping, Open Chen, Open Yang, Sun Taiji, Internal Other,
                ret = [180, 210];
            } else if (36 == formId) {
                // Other Tiaji Sword
                ret = [120, 300];
            } else if (38 == formId) {
                // Interal Other Weapon, Other Taiji Sword
                ret = [120, 180];
            } else if (30 == formId) {
                // Xingyiquan
                ret = [60, 120];
            }
        } else if (39 <= formId && formId <= 41) {
            // Group Sets (not sparring)
            ret = [45, 360];
        } else if (42 == formId) {
            // Sparring set
            ret = [45, 120];
        } else if (43 <= formId && formId <= 48) {
            // Push hands -- N/A
        }
        return ret;
    },

    getPenaltyTimeInterval: function (formId) {
        var interval = null;
        if (7 <= formId && formId <= 16) {
            interval = 2;
        } else {
            interval = 5;
        }
        return interval;
    },

    parseSeconds : function (timeString) {
        var splitTime = timeString.split(":");
        var seconds = null;
        if (1 == splitTime.length) {
            seconds = parseFloat(splitTime[0]);
        } else if (2 == splitTime.length) {
            seconds = (parseInt(splitTime[0]) * 60) + parseFloat(splitTime[1]);
        }
        return seconds;
    },

    formatSeconds : function (seconds) {
        var time = null;
        if (null == seconds) {
            time = "n/a";
        } else {
            var m = Math.floor(seconds / 60);
            var s = seconds - (m * 60);
            if (1 == (s + "").length) {
                s = "0" + s;
            }
            time = m + ":" + s;
        }
        return time;
    },

    sortEventSummary : function (eventA, eventB) {
        return eventA.event_order - eventB.event_order;
    },

    // Rounds a number off to the 1/place-th place
    formatFloat : function (n, place) {
        return Math.round(n * place) / place;
    },

    convertDbBoolean : function (val) {
        return 't' == val;
    }
};
