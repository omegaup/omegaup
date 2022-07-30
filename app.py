from flask import Flask, render_template, request, redirect, url_for, session, Response, flash, jsonify
from difflib import SequenceMatcher
from werkzeug.utils import secure_filename
import random
app = Flask(__name__)
import os

@app.route("/", methods=["POST", "GET"])
def index():
    return render_template('index.html')
@app.route('/getdata', methods=['POST'])
def getdata():
    try:
        content = request.get_json()
        # data processing code
        # content['p'] -> textarea 1 content
        # content['q'] -> textarea 2 content
        similarity = SequenceMatcher(None, content['p'], content['q']).ratio()
        similarity = similarity * 100
        similarity = int(similarity)
        sim=str(similarity)
        return jsonify({
            'status': True,
            'percentage': similarity,
            'result': "Your data is measured to be nearly "+ sim +"% matchably"
        })

    except Exception as e:
        print(e)
        return jsonify({
            'status': False,
            'result': "Error in processing"
            })






if __name__ == '__main__':
    app.run(debug=True)
