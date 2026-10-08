{# This is based on arena.problem.tpl which has similar functionality #}
{% extends "common.tpl" %}

{% block content %}
<script type="text/json" id="payload">{{ payload|json_encode|raw }}</script>
{% entrypoint %}
<div id="main-container"></div>
{% endblock %}
