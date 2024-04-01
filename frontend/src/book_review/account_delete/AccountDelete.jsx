import React, { useEffect, useState } from 'react';
import { useCookies } from 'react-cookie';
import { Link, useNavigate } from 'react-router-dom';
import axios from 'axios';
import defaultIcon from '../../defaultIcon.png';
import { useUrl } from '../../useUrl';
import { Header } from '../header/Header';
import './AccountDelete.scss';


export const AccountDelete = () => {
  const navigate = useNavigate(); // アカウント削除後、ログイン画面に遷移したいのでuseNavigateフックを使用
  const [cookies, , removeCookie] = useCookies();
  const [userName, setUserName] = useState('');
  const [keyword, setKeyword] = useState('');
  const [icon, setIcon] = useState(defaultIcon);
  const getUserUrl = useUrl('userOperation'); //カスタムフック。このコンポーネントで使うapiのurlが返る
  const deleteUserUrl = useUrl('userOperation');
  const handleKeywordChange = (event) => setKeyword(event.target.value);

  const headers = {
    authorization: `Bearer ${cookies.token}`
  };

  useEffect(() => {
    axios
    .get(getUserUrl, { headers })
    .then((response) => {
        setUserName(response.data.name);
        if (response.data.image_url !== null) {
          setIcon(response.data.image_url);
        }
    })
    .catch((error) => {
      alert(`ユーザー情報の取得に失敗しました。${error}`);
    });
  }, []);

  const handleDelete = (event) => {
    event.preventDefault();

    axios
      .delete(deleteUserUrl, { headers })
      .then(() => {
        removeCookie('token');
        navigate('/');
        window.location.reload();
      })
      .catch((error) => {
        alert(`ユーザー情報の削除に失敗しました。${error}`);
    })
  }

  return (
    <div>
      <Header />
      <div className="account-delete-page">
        <div className="account-delete-page_float">
          <p className="user-name">
            <img src={icon} alt="ユーザーのアイコン画像" className="user-icon" />
            {userName}
          </p>
          <div className='account-delete-navigate-message'>
            上記のアカウントを削除するためには下記の入力欄に「削除」と入力してください。
          </div>
          <form onSubmit={handleDelete}>
            <input
                type='text'
                placeholder='削除'
                onChange={handleKeywordChange}
                className='account-delete-input'
            />
            <br />
            <Link to={'/'}>
                <button className="return-button">戻る</button>
            </Link>
            {keyword === '削除' ?
                <button type="submit" className="account-delete-button">削除</button>
                :
                <></>
            }
          </form>
        </div>
      </div>
    </div>
  );
};
