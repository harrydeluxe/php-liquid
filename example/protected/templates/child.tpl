{% comment %} This is the child template. {% endcomment %}
{% extends "base" %}

{% block content %}
	<h2>Entry one</h2>
    <p>This is my first entry.</p>
{% endblock %}

{% block footer %}
	{{ document.copyright }}
{% endblock %}