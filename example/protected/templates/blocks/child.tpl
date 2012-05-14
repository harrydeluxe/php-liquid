{% comment %} This is the child template. {% endcomment %}
{% extends "base.tpl" %}

{% block footer %}
	{{ document.copyright }}
{% endblock %}