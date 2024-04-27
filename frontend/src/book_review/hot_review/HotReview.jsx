import React, { useState, useEffect } from 'react';
import { useCookies } from 'react-cookie';
import { IconContext } from 'react-icons';
import { FaHeart } from 'react-icons/fa';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { useUrl } from '../../useUrl';
import './HotReview.scss';

export const HotReview = () => {
  const [Reviews, setReviews] = useState([]);
  const [errorMessage, setErrorMessage] = useState();
  const [cookies] = useCookies();
  const getHotReviewUrl = useUrl('hotReview'); //カスタムフック。このコンポーネントで使うapiのurlが返る

  const headers = {
    authorization: `Bearer ${cookies.token}`
  };

  //useEffect(「ここにasync入れたらダメ。」())
  useEffect(() => {
    axios
      .get(getHotReviewUrl, { headers })
      .then((response) => {
        setReviews(response.data);
      })
      .catch((error) => {
        setErrorMessage(`書籍の取得に失敗しました${error}`);
      });
  }, []);

  return (
    <div className="page">
        <div className="hot_review_component">
          <font className="hot_review_title">いいねが多い投稿TOP3</font>
          <p className="error-message">{errorMessage}</p>
          <ul>
            {Reviews.map((ReviewList, key) => (
              <li key={key} className="Book__list" value={ReviewList.id}>
                <Link to={`/detail/${ReviewList.id}`} className="Book__list--link">
                  {ReviewList.title}
                  <br />
                  <div className='likes_home'>
                    <IconContext.Provider value={{ color: '#ff69b4', size: '25px' }}>
                      <FaHeart />
                      <span className='review_likes_count_home'>{ReviewList.likes}</span>
                    </IconContext.Provider>
                  </div>
                </Link>
              </li>
            ))}
          </ul>
        </div>
    </div>
  );
};

export default HotReview;