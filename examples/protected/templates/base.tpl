{% comment %} This is the base template. {% endcomment %}
<!DOCTYPE HTML>
<html>
    <head>
        {% include 'header.tpl' %}
    </head>
    <body>
		<div id="content">{% block content %}{% endblock %}</div>	
		<div id="footer">
			{% block footer %}
				&copy; Copyright 2014 by <a href="http://guzalexander.com/">Guz Alexander</a>.
			{% endblock %}
		</div>
    </body>
</html>
