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
    }
};
