import React, { useEffect, useState } from 'react';
import { useCookies } from 'react-cookie';
import { useSelector, useDispatch } from 'react-redux';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { signOut } from '../../authSlice';
import defaultIcon from '../../defaultIcon.png';
import { useUrl } from '../../useUrl';
import './header.scss';

export const Header = () => {
  const auth = useSelector((state) => state.auth.isSignIn);
  const dispatch = useDispatch();
  const [cookies, , removeCookie] = useCookies();
  const [userName, setUserName] = useState('');
  const [icon, setIcon] = useState(defaultIcon);
  const getUserUrl = useUrl('userOperation'); //カスタムフック。このコンポーネントで使うapiのurlが返る

  const handleSignOut = () => {
    dispatch(signOut());
    removeCookie('token');
  };

  const headers = {
    authorization: `Bearer ${cookies.token}`
  };

  useEffect(() => {
    //ログインしていたらユーザー情報を取得する
    if (auth) {
      axios
        .get(getUserUrl, { headers })
        .then((response) => {
          setUserName(response.data.name);
          if (response.data.image_url !== null) {
            setIcon(response.data.image_url);
          }
        })
        .catch((error) => {
          alert(`ヘッダーのユーザー情報の取得に失敗しました。${error}`);
        });
    }
  }, []);

  return (
    <header className="header">
      <Link to={'/'} className="Navigate-home">
        <h1>書籍レビュー</h1>
      </Link>
      {auth ? (
        <div className="userContainer">
          <Link to={'/new'} className="Navigate-button">
            書籍レビュー投稿画面へ
          </Link>
          <Link to={'/edit/profile'} className="Navigate-button" state={userName}>
            ユーザー名変更
          </Link>
          <Link to={'/login'} onClick={handleSignOut} className="Navigate-button">
            ログアウト
          </Link>
          <br />
          <p className="userName">
            ユーザー名：{userName}
            <img src={icon} alt="ユーザーのアイコン画像" className="userIcon" />
          </p>
        </div>
      ) : (
        <Link to={'/login'} className="Navigate-Login">
          ログイン
        </Link>
      )}
    </header>
  );
};
