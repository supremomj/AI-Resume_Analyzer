# AI Quick Cheat Sheet - One Page Reference

## 🤖 YOUR AI MODEL

**Model**: `all-MiniLM-L6-v2`  
**Type**: SentenceTransformer (Pre-trained)  
**Source**: Hugging Face  
**Size**: ~80MB, 384 dimensions  
**Training**: Pre-trained (NOT by you)  
**Library**: `sentence-transformers`

---

## 🎯 KEY POINTS

✅ **100% Pre-trained** - No custom training  
✅ **Transfer Learning** - General model → Your domain  
✅ **Semantic Matching** - Understands meaning, not keywords  
✅ **Zero-shot** - Works without training on your data  
✅ **Cost-effective** - No GPU, CPU-based  

---

## 💡 HOW IT WORKS

1. **Text → Embedding**: Converts text to 384-dim vector
2. **Compare Vectors**: Cosine similarity between embeddings
3. **Match Score**: 0-100% (filter <40%)

**Example**: "Software Engineer" ≈ "Developer" (semantic match)

---

## 🔧 THREE MAIN USES

1. **Role Recommendation**: Resume → Best career field
2. **Skill Recommendations**: Current skills → Skills to learn
3. **Job Matching**: Resume vs Job → Match score

---

## 📊 TECHNICAL SPECS

- **Embedding**: 384 dimensions
- **Similarity**: Cosine similarity
- **Speed**: 2-5 sec resume analysis
- **Threshold**: 40% minimum match
- **Fallback**: Keyword matching if AI fails

---

## 🎤 QUICK ANSWERS

**Why pre-trained?**  
→ Cost-effective, proven, fast deployment, transfer learning

**How accurate?**  
→ Semantic > keywords, handles synonyms, [X]% accuracy

**Limitations?**  
→ May miss specialized terms, depends on external sites, English-optimized

**How different?**  
→ Semantic understanding + Philippine data + Multi-source

---

## 🛠️ OTHER COMPONENTS

- **pyresparser**: Resume extraction (uses spaCy)
- **spaCy**: NLP pipeline (`en_core_web_sm`)
- **No custom training**: All pre-trained

---

## 📝 KEY PHRASES

- "Pre-trained transformer models"
- "Semantic similarity through embeddings"
- "Transfer learning approach"
- "384-dimensional vector representations"
- "Cosine similarity calculation"

---

**Remember**: You understand your system! Be confident! 🎓
