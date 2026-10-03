import json

with open('/Users/sinan/.gemini/antigravity/brain/78467d7d-74b5-407c-a6d0-69e95f786748/.system_generated/logs/transcript.jsonl', 'r') as f:
    for line in f:
        try:
            data = json.loads(line)
            if data.get('source') == 'USER_EXPLICIT' and 'content' in data:
                print("-------")
                print(data['content'])
        except Exception:
            pass
