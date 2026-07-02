(function () {
    function updateConditional(toggle) {
        var target = toggle.getAttribute('data-conditional-target');
        if (!target) {
            return;
        }

        var field = document.querySelector(target);
        if (!field) {
            return;
        }

        field.classList.toggle('is-visible', toggle.checked);
        field.querySelectorAll('input, textarea, select').forEach(function (input) {
            input.disabled = !toggle.checked;
        });
    }

    function updateConditionalGroup(toggle) {
        var group = toggle.name ? document.querySelectorAll('[name="' + toggle.name + '"][data-conditional-target]') : [toggle];
        group.forEach(function (item) {
            updateConditional(item);
        });
    }

    document.querySelectorAll('[data-conditional-target]').forEach(function (toggle) {
        updateConditional(toggle);
        toggle.addEventListener('change', function () {
            updateConditionalGroup(toggle);
        });
    });

    var profileSelector = document.querySelector('[data-profile-selector]');
    var profileData = document.getElementById('profile-data');

    if (profileSelector && profileData) {
        var profiles = {};
        try {
            profiles = JSON.parse(profileData.textContent || '{}');
        } catch (error) {
            profiles = {};
        }

        profileSelector.addEventListener('change', function () {
            var selected = profiles[profileSelector.value];
            if (!selected) {
                return;
            }

            Object.keys(selected).forEach(function (name) {
                var input = document.querySelector('[name="' + name + '"]');
                if (input) {
                    input.value = selected[name] || '';
                }
            });
        });
    }
})();
