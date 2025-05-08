async function translateText(text, targetLang) {
    const requestBody = {
    texts: [text],
    target_language: targetLang
    };
    const response = await fetch('http://127.0.0.1:5000/translate', {
    method: 'POST',
    headers: {
    'Content-Type': 'application/json',
    },
    body: JSON.stringify(requestBody),
    });
    if (!response.ok) {
    const errorData = await response.json();
    console.error('API Error:', errorData);
    throw new Error(`API Error: ${response.status} - ${response.statusText}`);
    }
    const data = await response.json();
    return data.translated_texts[0];
    }

    document.getElementById('language-selector').addEventListener('change', async function () {
        const lang = this.value;
        const title = document.getElementById('title').innerText;
        const description = document.getElementById('description').innerText;
        try {
        const translatedTitle = await translateText(title, lang);
        document.getElementById('title').innerText = translatedTitle;
        const translatedDescription = await translateText(description, lang);
        document.getElementById('description').innerText = translatedDescription;
        } catch (error) {
        console.error('Translation failed:', error);
        alert('Translation failed. Please check the console for details.');
        }
        });