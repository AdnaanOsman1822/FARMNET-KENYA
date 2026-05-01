from flask import Flask, request, jsonify
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

# Initialize Flask
app = Flask(__name__)

# Load Q&A dataset
data = pd.read_csv("dataset.csv")  
vectorizer = TfidfVectorizer()
X = vectorizer.fit_transform(data['question'])

@app.route('/get-response', methods=['POST'])
def get_response():
    user_input = request.json.get("message", "").strip()

    if not user_input:
        return jsonify({"response": "Please type something."})

    user_vec = vectorizer.transform([user_input])
    similarity = cosine_similarity(user_vec, X)
    best_match_index = similarity.argmax()

    

    answer = data.iloc[best_match_index]["answer"]
    return jsonify({"response": answer})

if __name__ == '__main__':
    app.run(port=5000)

