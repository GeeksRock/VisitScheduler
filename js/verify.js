var verify = new Vue({
    el: "#verify",
    data: {
        temp_password: "",
        new_password: "",
        confirm_new_password: ""
    },
    computed: {
        canLogin: function () {
            if (!this.temp_password || !this.new_password || !this.confirm_new_password) { return false; }
            return this.isValidPassword;
        },
        isValidPassword: function () {
            if (!(this.new_password.trim() === this.confirm_new_password.trim())) { return false; }
            if (!utils.isValidPassword(this.new_password)) { return false; }
            return true;
        },
        special_characters: function () {
            var special_characters = "Valid special characters: \n";
            special_characters = utils.stringBuilder(special_characters, "!   @   #   $   %   &   _");
            /* ! 49    @ 50    # 51    $ 52    % 53    & 55    _ 189 */
            return special_characters;
        }
    },
    methods: {
        nospaces: function (t) {
            t.currentTarget.value = t.currentTarget.value.trim();
        }
    }
});
