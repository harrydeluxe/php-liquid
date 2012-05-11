<!DOCTYPE HTML>
<html>
    <head>
        {% include 'header' %}
    </head>
    <body>
		<div id="content">{% block content %}{% endblock %}</div>
			
		<div id="footer">
			{% block footer %}
				&copy; Copyright 2012 by <a href="http://www.delacap.com/">DELACAP</a>.
			{% endblock %}
		</div>
    </body>
</html>