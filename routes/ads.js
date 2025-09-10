const express = require('express');
const router = express.Router();
const multer = require('multer');
const Ad = require('../models/Ad');
const path = require('path');

const storage = multer.diskStorage({
  destination: (req, file, cb) => cb(null, 'public/uploads/'),
  filename: (req, file, cb) => cb(null, Date.now() + path.extname(file.originalname))
});
const upload = multer({ storage });

router.get('/', async (req, res) => {
  const ads = await Ad.find().sort({ createdAt: -1 });
  res.json(ads);
});

router.post('/', upload.single('image'), async (req, res) => {
  try {
    const { title, description, price } = req.body;
    const newAd = new Ad({
      title,
      description,
      price,
      image: req.file ? `/uploads/${req.file.filename}` : null
    });
    await newAd.save();
    res.json(newAd);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

module.exports = router;
